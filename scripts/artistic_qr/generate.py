#!/usr/bin/env python3
"""Generate an artistic QR code with the logo woven into the modules (Amazing-QR)."""

from __future__ import annotations

import argparse
import contextlib
import io
import json
import shutil
import sys
import tempfile
from pathlib import Path


SUPPORTED_SUFFIXES = {".jpg", ".png", ".bmp", ".gif"}


def prepare_logo(logo_path: Path, work_dir: Path) -> Path:
    """Amazing-QR only accepts .jpg/.png/.bmp/.gif by exact 4-char suffix."""
    suffix = logo_path.suffix.lower()
    if suffix == ".jpeg":
        target = work_dir / f"{logo_path.stem}.jpg"
        shutil.copy2(logo_path, target)
        return target

    if suffix in SUPPORTED_SUFFIXES:
        # Copy into work dir so amzqr never writes next to the original logo.
        target = work_dir / f"logo{suffix}"
        shutil.copy2(logo_path, target)
        return target

    # Convert other formats (webp, etc.) to PNG via Pillow.
    from PIL import Image

    target = work_dir / "logo.png"
    image = Image.open(logo_path).convert("RGBA")
    image.save(target)
    return target


def generate(
    url: str,
    logo: Path,
    output: Path,
    version: int,
    contrast: float,
    brightness: float,
) -> Path:
    from amzqr import amzqr

    output.parent.mkdir(parents=True, exist_ok=True)

    with tempfile.TemporaryDirectory(prefix="artistic-qr-") as tmp:
        work_dir = Path(tmp)
        logo_ready = prepare_logo(logo, work_dir)
        out_tmp = work_dir / "artistic.png"

        amzqr.run(
            url,
            version=version,
            level="H",
            picture=str(logo_ready),
            colorized=True,
            contrast=contrast,
            brightness=brightness,
            save_name=out_tmp.name,
            save_dir=str(work_dir),
        )

        if not out_tmp.is_file():
            raise RuntimeError("Amazing-QR did not produce an output file.")

        shutil.copy2(out_tmp, output)

    return output


def main() -> int:
    parser = argparse.ArgumentParser(description="Artistic logo QR generator")
    parser.add_argument("--url", required=True)
    parser.add_argument("--logo", required=True)
    parser.add_argument("--output", required=True)
    parser.add_argument("--version", type=int, default=10)
    parser.add_argument("--contrast", type=float, default=1.15)
    parser.add_argument("--brightness", type=float, default=1.05)
    args = parser.parse_args()

    logo = Path(args.logo)
    output = Path(args.output)

    if not logo.is_file():
        print(json.dumps({"ok": False, "error": f"Logo not found: {logo}"}))
        return 1

    try:
        # Amazing-QR prints debug noise to stdout; keep our machine-readable JSON clean.
        buffer = io.StringIO()
        with contextlib.redirect_stdout(buffer):
            path = generate(
                url=args.url,
                logo=logo,
                output=output,
                version=max(1, min(40, args.version)),
                contrast=args.contrast,
                brightness=args.brightness,
            )
    except Exception as exc:  # noqa: BLE001 - surface to PHP bridge
        print(json.dumps({"ok": False, "error": str(exc)}))
        return 1

    print(json.dumps({"ok": True, "path": str(path)}))
    return 0


if __name__ == "__main__":
    sys.exit(main())

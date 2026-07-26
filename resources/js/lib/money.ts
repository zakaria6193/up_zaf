export type CurrencyCode =
    | 'MAD'
    | 'USD'
    | 'EUR'
    | 'GBP'
    | 'CAD'
    | 'AUD'
    | 'CHF'
    | 'AED'
    | 'SAR'
    | 'TND'
    | 'DZD'
    | 'EGP'
    | 'TRY'
    | 'JPY'
    | 'CNY'
    | 'INR'
    | 'BRL'
    | 'MXN'
    | string;

/**
 * Format a price using the business currency (ISO 4217).
 */
export function formatMoney(price: number | string, currency: CurrencyCode = 'MAD'): string {
    const amount = typeof price === 'string' ? Number(price) : price;
    const code = (currency || 'MAD').toUpperCase();

    try {
        return new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency: code,
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(Number.isFinite(amount) ? amount : 0);
    } catch {
        return `${Number.isFinite(amount) ? amount : 0} ${code}`;
    }
}

/**
 * Short label for forms (e.g. "MAD", "EUR").
 */
export function currencyLabel(currency: CurrencyCode = 'MAD'): string {
    return (currency || 'MAD').toUpperCase();
}

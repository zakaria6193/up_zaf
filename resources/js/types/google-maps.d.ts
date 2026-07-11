export {};

declare global {
    interface Window {
        google: {
            maps: {
                places: {
                    Autocomplete: new (
                        input: HTMLInputElement,
                        opts?: {
                            types?: string[];
                            componentRestrictions?: { country: string };
                        }
                    ) => {
                        addListener: (event: string, callback: () => void) => void;
                        getPlace: () => {
                            formatted_address?: string;
                            geometry?: {
                                location?: {
                                    lat: () => number;
                                    lng: () => number;
                                };
                            };
                        };
                    };
                };
            };
        };
    }
}

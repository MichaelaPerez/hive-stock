export interface Item {
	barcode: string;
	name: string;
	location: string;
	location_name: string;
}

export interface Location {
	barcode: string;
	name: string;
	parent_location: string | null;
	parent_location_name: string | null;
}

export interface NewItem {
	barcode: string;
	name: string;
	location: string;
}

export interface NewLocation {
	barcode: string;
	name: string;
	parent_location: string | null;
}

export interface ApiErrorBody {
	error?: {
		message?: string;
		code?: string;
	};
}

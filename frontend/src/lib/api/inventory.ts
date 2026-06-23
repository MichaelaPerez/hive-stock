import { ApiError, apiRequest } from './client';
import type { Item, Location, NewItem, NewLocation } from './types';

const encode = (value: string) => encodeURIComponent(value.trim());

export function getItems(): Promise<Item[]> {
	return apiRequest<Item[]>('/api/v1/items');
}

export function getItemByBarcode(barcode: string): Promise<Item> {
	return apiRequest<Item>(`/api/v1/items/barcode/${encode(barcode)}`);
}

export function getItemByName(name: string): Promise<Item> {
	return apiRequest<Item>(`/api/v1/items/name/${encode(name)}`);
}

export async function searchItem(query: string): Promise<Item> {
	try {
		return await getItemByBarcode(query);
	} catch (error) {
		if (error instanceof ApiError && error.status === 404) {
			return getItemByName(query);
		}

		throw error;
	}
}

export function createItem(item: NewItem): Promise<Item> {
	return apiRequest<Item>('/api/v1/items', {
		method: 'POST',
		body: JSON.stringify(item)
	});
}

export function updateItemLocation(barcode: string, location: string): Promise<Item> {
	return apiRequest<Item>(`/api/v1/items/${encode(barcode)}`, {
		method: 'PATCH',
		body: JSON.stringify({ location })
	});
}

export function getLocations(): Promise<Location[]> {
	return apiRequest<Location[]>('/api/v1/locations');
}

export function getLocationByBarcode(barcode: string): Promise<Location> {
	return apiRequest<Location>(`/api/v1/locations/barcode/${encode(barcode)}`);
}

export function getLocationByName(name: string): Promise<Location> {
	return apiRequest<Location>(`/api/v1/locations/name/${encode(name)}`);
}

export async function searchLocation(query: string): Promise<Location> {
	try {
		return await getLocationByBarcode(query);
	} catch (error) {
		if (error instanceof ApiError && error.status === 404) {
			return getLocationByName(query);
		}

		throw error;
	}
}

export function getItemsInLocation(barcode: string): Promise<Item[]> {
	return apiRequest<Item[]>(`/api/v1/locations/${encode(barcode)}/items`);
}

export function createLocation(location: NewLocation): Promise<Location> {
	return apiRequest<Location>('/api/v1/locations', {
		method: 'POST',
		body: JSON.stringify(location)
	});
}

export function updateLocationParent(
	barcode: string,
	parent_location: string | null
): Promise<Location> {
	return apiRequest<Location>(`/api/v1/locations/${encode(barcode)}`, {
		method: 'PATCH',
		body: JSON.stringify({ parent_location })
	});
}

import type { ApiErrorBody } from './types';

const API_BASE = import.meta.env.VITE_API_BASE ?? '';

export class ApiError extends Error {
	constructor(
		message: string,
		public readonly status: number,
		public readonly code: string
	) {
		super(message);
		this.name = 'ApiError';
	}
}

export async function apiRequest<T>(path: string, options: RequestInit = {}): Promise<T> {
	const response = await fetch(`${API_BASE}${path}`, {
		...options,
		headers: {
			'Content-Type': 'application/json',
			...options.headers
		}
	});

	const body = (await response.json().catch(() => ({}))) as ApiErrorBody & { data?: T };

	if (!response.ok) {
		throw new ApiError(
			body.error?.message ?? 'Request failed',
			response.status,
			body.error?.code ?? 'request_failed'
		);
	}

	return body.data as T;
}

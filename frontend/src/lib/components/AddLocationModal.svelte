<script lang="ts">
	import { createEventDispatcher } from 'svelte';
	import { ApiError } from '$lib/api/client';
	import { createLocation } from '$lib/api/inventory';
	import type { Location } from '$lib/api/types';
	import Modal from './Modal.svelte';

	export let open = false;
	export let locations: Location[] = [];

	let barcode = '';
	let name = '';
	let parentLocation = '';
	let saving = false;
	let error = '';

	const dispatch = createEventDispatcher<{ close: void; created: Location }>();

	async function submit() {
		error = '';
		saving = true;

		try {
			const created = await createLocation({
				barcode: barcode.trim(),
				name: name.trim(),
				parent_location: parentLocation === '' ? null : parentLocation
			});

			barcode = '';
			name = '';
			parentLocation = '';
			dispatch('created', created);
		} catch (caught) {
			error = caught instanceof ApiError ? caught.message : 'Unable to add location';
		} finally {
			saving = false;
		}
	}
</script>

<Modal {open} title="Add New Location" on:close={() => dispatch('close')}>
	<form class="modal-form" on:submit|preventDefault={submit}>
		<label>
			<span>Barcode</span>
			<input bind:value={barcode} required placeholder="LOC-PANTRY" />
		</label>

		<label>
			<span>Name</span>
			<input bind:value={name} required placeholder="Pantry" />
		</label>

		<label>
			<span>Parent location</span>
			<select bind:value={parentLocation}>
				<option value="">No parent location</option>
				{#each locations as option}
					<option value={option.barcode}>{option.name} ({option.barcode})</option>
				{/each}
			</select>
		</label>

		{#if error}
			<p class="error-text">{error}</p>
		{/if}

		<footer>
			<button type="button" class="secondary-button" on:click={() => dispatch('close')}>Cancel</button>
			<button type="submit" disabled={saving}>{saving ? 'Adding...' : 'Add Location'}</button>
		</footer>
	</form>
</Modal>

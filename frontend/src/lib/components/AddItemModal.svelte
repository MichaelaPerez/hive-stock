<script lang="ts">
	import { createEventDispatcher } from 'svelte';
	import { ApiError } from '$lib/api/client';
	import { createItem } from '$lib/api/inventory';
	import type { Item, Location } from '$lib/api/types';
	import Modal from './Modal.svelte';

	export let open = false;
	export let locations: Location[] = [];

	let barcode = '';
	let name = '';
	let location = '';
	let saving = false;
	let error = '';

	const dispatch = createEventDispatcher<{ close: void; created: Item }>();

	$: if (locations.length > 0 && location === '') {
		location = locations[0].barcode;
	}

	async function submit() {
		error = '';
		saving = true;

		try {
			const created = await createItem({
				barcode: barcode.trim(),
				name: name.trim(),
				location
			});

			barcode = '';
			name = '';
			dispatch('created', created);
		} catch (caught) {
			error = caught instanceof ApiError ? caught.message : 'Unable to add item';
		} finally {
			saving = false;
		}
	}
</script>

<Modal {open} title="Add New Item" on:close={() => dispatch('close')}>
	<form class="modal-form" on:submit|preventDefault={submit}>
		<label>
			<span>Barcode</span>
			<input bind:value={barcode} required placeholder="ITEM-FLASHLIGHT" />
		</label>

		<label>
			<span>Name</span>
			<input bind:value={name} required placeholder="Flashlight" />
		</label>

		<label>
			<span>Location</span>
			<select bind:value={location} required disabled={locations.length === 0}>
				{#each locations as option}
					<option value={option.barcode}>{option.name} ({option.barcode})</option>
				{/each}
			</select>
		</label>

		{#if locations.length === 0}
			<p class="error-text">Add a location before adding items.</p>
		{/if}

		{#if error}
			<p class="error-text">{error}</p>
		{/if}

		<footer>
			<button type="button" class="secondary-button" on:click={() => dispatch('close')}>Cancel</button>
			<button type="submit" disabled={saving || locations.length === 0}>
				{saving ? 'Adding...' : 'Add Item'}
			</button>
		</footer>
	</form>
</Modal>

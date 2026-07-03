<script lang="ts">
	import { onMount } from 'svelte';
	import { ApiError } from '$lib/api/client';
	import { getItemsInLocation, getLocations, getItems, searchItem, searchLocation } from '$lib/api/inventory';
	import type { Item, Location } from '$lib/api/types';
	import AddItemModal from '$lib/components/AddItemModal.svelte';
	import AddLocationModal from '$lib/components/AddLocationModal.svelte';
	import ItemCard from '$lib/components/ItemCard.svelte';
	import LocationCard from '$lib/components/LocationCard.svelte';
	import AllItemsCard from '$lib/components/AllItemsCard.svelte';
	import SearchBar from '$lib/components/SearchBar.svelte';

	let locations: Location[] = [];
	let selectedItem: Item | null = null;
	let selectedLocation: Location | null = null;
	let locationItems: Item[] = [];
	let allItems: Item[] = [];

	let itemLoading = false;
	let locationLoading = false;
	let allItemsLoading = false;
	let itemError = '';
	let locationError = '';
	let allItemsError = '';
	let pageError = '';
	let toast = '';

	let addItemOpen = false;
	let addLocationOpen = false;
	let allItemsShow = false;

	let itemTable = [
		{
			'Item' : 'Item',
			'Location' : 'Location'
		}
	];

	onMount(() => {
		void refreshLocations();
		void refreshAllItems();
	});

	async function refreshLocations() {
		try {
			locations = await getLocations();
			pageError = '';
		} catch (error) {
			pageError = messageFor(error, 'Unable to load locations');
		}
	}

	async function refreshAllItems() {
		try {
			allItems = await getItems();
			fillItemTable();
			pageError = '';
		} catch (error) {
			pageError = messageFor(error, 'Unable to load all items');
		}
	}

	function fillItemTable() {
		allItems.forEach((item) => {
			itemTable.push({'Item': item.name, 'Location': item.location_name});
		});
	}

	async function handleItemSearch(query: string) {
		itemLoading = true;
		itemError = '';
		selectedItem = null;

		try {
			selectedItem = await searchItem(query);
		} catch (error) {
			itemError = messageFor(error, 'Unable to search items');
		} finally {
			itemLoading = false;
		}
	}

	async function handleLocationSearch(query: string) {
		locationLoading = true;
		locationError = '';
		selectedLocation = null;
		locationItems = [];

		try {
			selectedLocation = await searchLocation(query);
			locationItems = await getItemsInLocation(selectedLocation.barcode);
		} catch (error) {
			locationError = messageFor(error, 'Unable to search locations');
		} finally {
			locationLoading = false;
		}
	}

	function handleItemCreated(event: CustomEvent<Item>) {
		selectedItem = event.detail;
		addItemOpen = false;
		showToast('Item added successfully.');
		refreshAllItems();
	}

	async function handleLocationCreated(event: CustomEvent<Location>) {
		selectedLocation = event.detail;
		locationItems = [];
		addLocationOpen = false;
		showToast('Location added successfully.');
		await refreshLocations();
	}

	function showToast(message: string) {
		toast = message;
		window.setTimeout(() => {
			if (toast === message) {
				toast = '';
			}
		}, 3500);
	}

	function messageFor(error: unknown, fallback: string) {
		return error instanceof ApiError ? error.message : fallback;
	}
</script>

<svelte:head>
	<title>Hive Stock | Home Inventory</title>
</svelte:head>

<main class="page-shell">
	<section class="hero-card">
		<div>
			<p class="eyebrow">Home Inventory</p>
			<h1>Hive Stock</h1>
			<p>Find exactly what you own and where it lives by barcode or exact name.</p>
		</div>
	</section>

	{#if pageError}
		<p class="banner error-text">{pageError}</p>
	{/if}

	{#if toast}
		<p class="banner success-text">{toast}</p>
	{/if}

	<section class="search-panel">
		<SearchBar
			label="Item search"
			placeholder="Scan barcode or enter item name"
			loading={itemLoading}
			on:search={(event) => handleItemSearch(event.detail)}
		/>
		<SearchBar
			label="Location search"
			placeholder="Scan barcode or enter location name"
			loading={locationLoading}
			on:search={(event) => handleLocationSearch(event.detail)}
		/>
	</section>

	{#if allItemsShow} 
		<AllItemsCard itemTable={itemTable} loading={allItemsLoading} error={allItemsError}/> 
	{:else}
		<ItemCard item={selectedItem} loading={itemLoading} error={itemError} />
		<LocationCard
			location={selectedLocation}
			items={locationItems}
			loading={locationLoading}
			error={locationError}
		/>
	{/if}

	<section class="action-row" aria-label="Inventory actions">
		<button type="button" class="accent-button" on:click={() => (addItemOpen = true)}>
			Add New Item
		</button>
		<button type="button" class="accent-button" on:click={() => (addLocationOpen = true)}>
			Add New Location
		</button>
		<button type="button" class="accent-button" on:click={() => (allItemsShow=!allItemsShow)}>
			Show/Hide All Items
		</button>
	</section>
</main>

<AddItemModal
	open={addItemOpen}
	{locations}
	on:close={() => (addItemOpen = false)}
	on:created={handleItemCreated}
/>

<AddLocationModal
	open={addLocationOpen}
	{locations}
	on:close={() => (addLocationOpen = false)}
	on:created={handleLocationCreated}
/>

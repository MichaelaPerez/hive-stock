<script lang="ts">
	import type { Item } from '$lib/api/types';

	export let item: Item | null = null;
	export let loading = false;
	export let error = '';
</script>

<section class:item-card-selected={item} class="result-card">
	<header>
		<h2>Item</h2>
	</header>

	{#if loading}
		<p class="empty-state">Looking for that item...</p>
	{:else if error}
		<p class="error-text">{error}</p>
	{:else if item}
		<div class="detail-grid">
			<span>Name</span>
			<strong>{item.name}</strong>

			<span>Barcode</span>
			<strong>{item.barcode}</strong>

			<span>Location</span>
			<strong>{item.location_name} ({item.location})</strong>
		</div>
	{:else}
		<p class="empty-state">Search by item barcode or exact item name to display item details.</p>
	{/if}
</section>

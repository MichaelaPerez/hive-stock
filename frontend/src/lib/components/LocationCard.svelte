<script lang="ts">
	import type { Item, Location } from '$lib/api/types';

	export let location: Location | null = null;
	export let items: Item[] = [];
	export let loading = false;
	export let error = '';
</script>

<section class:location-card-selected={location} class="result-card">
	<header>
		<h2>Location</h2>
	</header>

	{#if loading}
		<p class="empty-state">Looking for that location...</p>
	{:else if error}
		<p class="error-text">{error}</p>
	{:else if location}
		<div class="detail-grid">
			<span>Name</span>
			<strong>{location.name}</strong>

			<span>Barcode</span>
			<strong>{location.barcode}</strong>

			<span>Parent</span>
			<strong>
				{location.parent_location_name
					? `${location.parent_location_name} (${location.parent_location})`
					: 'No parent location'}
			</strong>
		</div>

		<div class="location-items">
			<h3>Items here</h3>
			{#if items.length > 0}
				<ul>
					{#each items as item}
						<li>{item.name} <span>{item.barcode}</span></li>
					{/each}
				</ul>
			{:else}
				<p class="empty-state">No items are stored directly in this location.</p>
			{/if}
		</div>
	{:else}
		<p class="empty-state">Search by location barcode or exact location name to display location details.</p>
	{/if}
</section>

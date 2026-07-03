<script lang="ts">
    import Table from '$lib/components/Table.svelte';

	export let itemTable = [
		{
			'Item' : 'Item',
			'Location' : 'Location'
		}
	];
	export let loading = false;
	export let error = '';
</script>

<section class="result-card">
	<header>
		<h2>All Items</h2>
	</header>

	{#if loading}
		<p class="empty-state">Looking for all items...</p>
	{:else if error}
		<p class="error-text">{error}</p>
	{:else if location}
		<div>
			{#if itemTable.length > 0}
                <Table tableData={itemTable}/>
			{:else}
				<p class="empty-state">No items are stored.</p>
			{/if}
		</div>
	{:else}
		<p class="empty-state">Don't look at me.</p>
	{/if}
</section>

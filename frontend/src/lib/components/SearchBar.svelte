<script lang="ts">
	import { createEventDispatcher } from 'svelte';

	export let label: string;
	export let placeholder: string;
	export let loading = false;

	let query = '';
	const dispatch = createEventDispatcher<{ search: string }>();

	function submit() {
		const trimmed = query.trim();

		if (trimmed !== '') {
			dispatch('search', trimmed);
		}
	}
</script>

<form class="search-bar" on:submit|preventDefault={submit}>
	<label>
		<span>{label}</span>
		<input bind:value={query} type="search" {placeholder} />
	</label>
	<button type="submit" disabled={loading || query.trim() === ''}>
		{loading ? 'Searching...' : 'Search'}
	</button>
</form>

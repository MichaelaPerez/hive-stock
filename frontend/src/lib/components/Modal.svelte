<script lang="ts">
	import { createEventDispatcher } from 'svelte';

	export let open = false;
	export let title: string;

	const dispatch = createEventDispatcher<{ close: void }>();
</script>

{#if open}
	<div class="modal-backdrop" role="presentation" on:click={() => dispatch('close')}>
		<section
			class="modal-panel"
			role="dialog"
			aria-modal="true"
			aria-labelledby="modal-title"
			on:click|stopPropagation
		>
			<header>
				<h2 id="modal-title">{title}</h2>
				<button type="button" class="icon-button" aria-label="Close modal" on:click={() => dispatch('close')}>
					x
				</button>
			</header>
			<slot />
		</section>
	</div>
{/if}

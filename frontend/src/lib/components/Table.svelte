<script>
	export let tableData = [
		{
			'Item' : 'Item',
			'Location' : 'Location'
		}
	];
</script>

<table>
	<thead>
		<tr>
			{#each Object.keys(tableData[0]) as columnHeading}
				<th class="table-header">{columnHeading}</th>
			{/each}
		</tr>
	</thead>
	<tbody>
		{#each Object.values(tableData) as row}
			<tr>
				{#each Object.values(row) as cell}
					<td>{cell}</td>
				{/each}
			</tr>
		{/each}
	</tbody>
</table>
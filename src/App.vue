<template>
	<div class="root flow">
		<transition name="fade" mode="out-in">
			<router-view />
		</transition>
	</div>
</template>

<style scoped>
.root {
	background-color: var(--neb-bg-1);
	color: var(--neb-textcolor);
}
</style>

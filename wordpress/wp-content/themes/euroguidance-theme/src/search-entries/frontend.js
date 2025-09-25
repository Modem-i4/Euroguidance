document.addEventListener('DOMContentLoaded', function () {
	const searchBlock = document.querySelector('.search-block');
	if (!searchBlock) return;

	const input = searchBlock.querySelector('.search-block__input');
	const emptyMessage = document.querySelector('.search-block__empty-message');
	const targetSelector = searchBlock.dataset.targetBlocks || '.materials-card';
	const cards = document.querySelectorAll(targetSelector);

	if (!input) return;

	input.addEventListener('input', function () {
		const query = this.value.trim().toLowerCase();
		let visibleCount = 0; 

		cards.forEach(card => {
			const text = card.textContent.toLowerCase();
			const matches = text.includes(query);
			card.style.display = matches ? '' : 'none';
			if (matches) visibleCount++;
		});
		if(!emptyMessage) return;
		emptyMessage.style.display = visibleCount === 0 ? 'block' : 'none';
	});
});

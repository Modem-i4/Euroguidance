import { registerBlockType, createBlock, parse } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { PanelBody, TextControl, Button, SelectControl, Notice } from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import metadata from './block.json';
import './style.scss';

/** Рекурсивно розгортає reusable (core/block with ref) у звичайні блоки */
function expandReusableBlocks(blocks, getReusableHTMLById) {
	return blocks.flatMap((b) => {
		if (b?.name === 'core/block' && b?.attributes?.ref) {
			const html = getReusableHTMLById(b.attributes.ref);
			if (html) {
				const inner = parse(html);
				return expandReusableBlocks(inner, getReusableHTMLById);
			}
			return [b];
		}
		if (b?.innerBlocks?.length) {
			const expandedChildren = expandReusableBlocks(b.innerBlocks, getReusableHTMLById);
			return [{ ...b, innerBlocks: expandedChildren }];
		}
		return [b];
	});
}

registerBlockType(metadata.name, {
	edit: ({ attributes, setAttributes, clientId }) => {
		const { addMode, blockName, patternKey, buttonLabel, targetSelector } = attributes;
		const blockProps = useBlockProps();

		const { insertBlocks } = useDispatch('core/block-editor');

		const be = useSelect((select) => select('core/block-editor'), []);
		const core = useSelect((select) => select('core'), []);

		const rootClientId = be.getBlockRootClientId(clientId);
		const currentIndex = be.getBlockIndex(clientId, rootClientId);

		// 1) шукаємо в зареєстрованих патернах за slug (name)
		const patternBySlug = useMemo(() => {
			if (!patternKey || addMode !== 'pattern') return null;
			const patterns = be.getBlockPatterns?.() || [];
			return patterns.find((p) => p.name === patternKey) || null;
		}, [addMode, patternKey, be]);

		// 2) якщо не знайдено і patternKey схожий на ID → тягнемо пост
		const isNumericId = useMemo(() => /^\d+$/.test((patternKey || '').trim()), [patternKey]);
		const patternPostById = useSelect(
			(select) => {
				if (!isNumericId || addMode !== 'pattern') return null;
				const coreSel = select('core');
				const id = Number(patternKey);
				// Спроба: спершу wp_pattern (нові кастомні патерни), інакше wp_block (synced)
				return (
					coreSel.getEntityRecord?.('postType', 'wp_pattern', id) ||
					coreSel.getEntityRecord?.('postType', 'wp_block', id) ||
					null
				);
			},
			[addMode, isNumericId, patternKey]
		);

		const getReusableHTMLById = (id) => {
			// reusable/synced традиційно зберігаються у wp_block
			const rec =
				core.getEntityRecord?.('postType', 'wp_block', id) ||
				core.getEntityRecord?.('postType', 'wp_pattern', id) ||
				null;
			return rec?.content?.raw || null;
		};

		const resolveTarget = () => {
			if (!targetSelector) return { parentId: rootClientId, index: currentIndex + 1 };
			const el = document.querySelector(targetSelector);
			if (!el) return { parentId: rootClientId, index: currentIndex + 1 };

			const host = el.closest('.block-editor-block-list__block');
			const targetId = host?.dataset?.block;
			if (!targetId) return { parentId: rootClientId, index: currentIndex + 1 };

			const order = be.getBlockOrder(targetId) || [];
			return { parentId: targetId, index: order.length }; // всередину, в кінець
		};

		const handleAdd = () => {
			let blocksToInsert = [];

			if (addMode === 'block') {
				blocksToInsert = [createBlock(blockName)];
			} else if (addMode === 'pattern') {
				if (patternBySlug?.content) {
					const parsed = parse(patternBySlug.content);
					blocksToInsert = expandReusableBlocks(parsed, getReusableHTMLById);
				} else if (isNumericId && patternPostById?.content?.raw) {
					const parsed = parse(patternPostById.content.raw);
					blocksToInsert = expandReusableBlocks(parsed, getReusableHTMLById);
				} else {
					return; // не знайшли патерн ні за slug, ні за id
				}
			}

			if (!blocksToInsert.length) return;

			const { parentId, index } = resolveTarget();
			insertBlocks(blocksToInsert, index, parentId);
		};

		const showNotFoundNotice =
			addMode === 'pattern' &&
			patternKey &&
			!patternBySlug &&
			(!isNumericId || (isNumericId && !patternPostById));

		return (
			<>
				<InspectorControls>
					<PanelBody title="Налаштування додавання">
						<SelectControl
							label="Режим додавання"
							value={addMode}
							options={[
								{ label: 'Блок за ім’ям', value: 'block' },
								{ label: 'Патерн (slug або ID)', value: 'pattern' },
							]}
							onChange={(val) => setAttributes({ addMode: val })}
						/>

						{addMode === 'block' && (
							<TextControl
								label="Назва блоку (namespace/block)"
								value={blockName}
								onChange={(val) => setAttributes({ blockName: val })}
								placeholder="core/paragraph"
							/>
						)}

						{addMode === 'pattern' && (
							<>
								<TextControl
									label="Pattern (slug або ID)"
									value={patternKey}
									onChange={(val) => setAttributes({ patternKey: val })}
									placeholder="theme/pattern-name або 123"
								/>
								{showNotFoundNotice && (
									<Notice status="warning" isDismissible={false}>
										Патерн не знайдено ні за slug, ні за ID.
									</Notice>
								)}
							</>
						)}

						<TextControl
							label="CSS-селектор таргета (опційно)"
							value={targetSelector}
							onChange={(val) => setAttributes({ targetSelector: val })}
							placeholder=".my-section або #target"
						/>

						<TextControl
							label="Текст кнопки"
							value={buttonLabel}
							onChange={(val) => setAttributes({ buttonLabel: val })}
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps} className="add-block-btn">
					<Button variant="primary" onClick={handleAdd}>{buttonLabel}</Button>
				</div>
			</>
		);
	},

	save: () => null,
});

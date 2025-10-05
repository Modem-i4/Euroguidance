import { useState, useMemo, useRef } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { InspectorControls } from '@wordpress/block-editor';
import {
	Button,
	Popover,
	TextControl,
	Tooltip,
	ToggleControl,
	PanelBody,
	__experimentalHStack as HStack,
	__experimentalSpacer as Spacer,
} from '@wordpress/components';
import { plus } from '@wordpress/icons';

const META_KEY = 'ntd_social_links';
const PLATFORMS = [
	{ type: 'website',   label: 'Вебсайт'   },
	{ type: 'linkedin',  label: 'LinkedIn'  },
	{ type: 'facebook',  label: 'Facebook'  },
	{ type: 'instagram', label: 'Instagram' },
];

/* --- SVG-іконки під currentColor --- */
const IconLinkedIn = () => (
	<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<path fill="currentColor" d="M19.7,3H4.3C3.582,3,3,3.582,3,4.3v15.4C3,20.418,3.582,21,4.3,21h15.4c0.718,0,1.3-0.582,1.3-1.3V4.3 C21,3.582,20.418,3,19.7,3z M8.339,18.338H5.667v-8.59h2.672V18.338z M7.004,8.574c-0.857,0-1.549-0.694-1.549-1.548 c0-0.855,0.691-1.548,1.549-1.548c0.854,0,1.547,0.694,1.547,1.548C8.551,7.881,7.858,8.574,7.004,8.574z M18.339,18.338h-2.669 v-4.177c0-.996-.017-2.278-1.387-2.278-1.389,0-1.601,1.086-1.601,2.206v4.249h-2.667v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703,0 3.203,1.779 3.203,4.092V18.338z"></path>
	</svg>
);
const IconInstagram = () => (
	<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<path fill="currentColor" d="M12,4.622c2.403,0,2.688,0.009,3.637,0.052c0.877,0.04,1.354,0.187,1.671,0.31c0.42,0.163,0.72,0.358,1.035,0.673 c0.315,0.315,0.51,0.615,0.673,1.035c0.123,0.317,0.27,0.794,0.31,1.671c0.043,0.949,0.052,1.234,0.052,3.637 s-0.009,2.688-0.052,3.637c-0.04,0.877-0.187,1.354-0.31,1.671c-0.163,0.42-0.358,0.72-0.673,1.035 c-0.315,0.315-0.615,0.51-1.035,0.673c-0.317,0.123-0.794,0.27-1.671,0.31c-0.949,0.043-1.233,0.052-3.637,0.052 s-2.688-0.009-3.637-0.052c-0.877-0.04-1.354-0.187-1.671-0.31c-0.42-0.163-0.72-0.358-1.035-0.673 c-0.315-0.315-0.51-0.615-0.673-1.035c-0.123-0.317-0.27-0.794-0.31-1.671C4.631,14.688,4.622,14.403,4.622,12 s0.009-2.688,0.052-3.637c0-0.877,0.187-1.354,0.31-1.671c0.163-0.42,0.358-0.72,0.673-1.035 c0.315-0.315,0.615-0.51,1.035-0.673c0.317-0.123,0.794-0.27,1.671-0.31C9.312,4.631,9.597,4.622,12,4.622 M12,3 C9.556,3,9.249,3.01,8.289,3.054C7.331,3.098,6.677,3.25,6.105,3.472C5.513,3.702,5.011,4.01,4.511,4.511 c-.5.5-.808,1.002-1.038,1.594C3.25,6.677,3.098,7.331,3.054,8.289 3.01,9.249,3,9.556,3,12c0,2.444.01,2.751.054,3.711.044.958.196,1.612.418,2.185.23.592.538,1.094,1.038,1.594.5.5,1.002.808,1.594,1.038.572.222,1.227.375,2.185.418C9.249,20.99,9.556,21,12,21s2.751-.01,3.711-.054c.958-.044,1.612-.196,2.185-.418.592-.23,1.094-.538,1.594-1.038.5-.5.808-1.002,1.038-1.594.222-.572.375-1.227.418-2.185C20.99,14.751,21,14.444,21,12s-.01-2.751-.054-3.711c-.044-.958-.196-1.612-.418-2.185-.23-.592-.538-1.094-1.038-1.594-.5-.5-1.002-.808-1.594-1.038-.572-.222-1.227-.375-2.185-.418C14.751,3.01,14.444,3,12,3z M12,7.378c-2.552,0-4.622,2.069-4.622,4.622S9.448,16.622,12,16.622s4.622-2.069,4.622-4.622S14.552,7.378,12,7.378z M12,15 c-1.657,0-3-1.343-3-3s1.343-3,3-3 3,1.343,3,3-1.343,3-3,3z M16.804,6.116c-.596,0-1.08.484-1.08,1.08s.484,1.08,1.08,1.08c.596,0,1.08-.484,1.08-1.08s-.327-1.08-1.08-1.08z"></path>
	</svg>
);
const IconFacebook = () => (
	<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7C18.3 21.1 22 17 22 12c0-5.5-4.5-10-10-10z"></path>
	</svg>
);
const IconWebsite = () => (
	<svg width="24" height="24" viewBox="0 0 420 420" aria-hidden="true" focusable="false">
		<path fill="none" stroke="currentColor" strokeWidth="26" d="M209,15a195,195 0 1,0 2,0z"/>
		<path fill="none" stroke="currentColor" strokeWidth="18" d="m210,15v390m195-195H15M59,90a260,260 0 0,0 302,0 m0,240 a260,260 0 0,0-302,0M195,20a250,250 0 0,0 0,382 m30,0 a250,250 0 0,0 0-382"/>
	</svg>
);

const ICONS = {
	website: IconWebsite,
	linkedin: IconLinkedIn,
	facebook: IconFacebook,
	instagram: IconInstagram,
};

export default function Edit( props ) {
	const { attributes, setAttributes, context = {} } = props;

	/* Отримуємо postId/postType навіть поза Query Loop */
	const fallback = useSelect( ( select ) => {
		const ed = select( 'core/editor' );
		return { postId: ed?.getCurrentPostId?.(), postType: ed?.getCurrentPostType?.() };
	}, [] );
	const postId   = context.postId   ?? fallback.postId;
	const postType = context.postType ?? fallback.postType;

	/* Мета */
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta', postId );

	/* Декодуємо масив посилань */
	const links = useMemo( () => {
		const raw = meta?.[ META_KEY ];
		if ( Array.isArray( raw ) ) return raw;
		if ( typeof raw === 'string' && raw.trim() ) {
			try { return JSON.parse( raw ); } catch( e ) {}
		}
		return [];
	}, [ meta ] );

	const setLinks = (next) => setMeta( { ...meta, [ META_KEY ]: next } );

	/* Локальний стан редактора */
	const [ editingIndex, setEditingIndex ] = useState( null );
	const [ pickerOpen, setPickerOpen ] = useState( false );
	const anchorRef = useRef( null );

	/* Додавання/редагування */
	const addPlatform = ( type ) => {
		if ( links.find( l => l.type === type ) ) return;
		const next = [ ...links, { type, url: '', showLabel: true } ];
		setLinks( next );
		setPickerOpen( false );
		setTimeout( () => setEditingIndex( next.length - 1 ), 0 );
	};

	const setUrlAt = ( i, url ) => {
		const next = links.map( (l,idx)=> idx===i?{...l,url}:l );
		setLinks( next );
	};

	const setShowLabelAt = ( i, value ) => {
		const next = links.map( (l,idx)=> idx===i?{...l, showLabel: !!value}:l );
		setLinks( next );
	};

	const removeAt = ( i ) => {
		setLinks( links.filter( (_l,idx)=>idx!==i ) );
		setEditingIndex( null );
	};

	const available = PLATFORMS.filter( p => !links.some( l => l.type === p.type ) );
	const globalShow = !!attributes?.showLabels;

	return (
		<>
			<InspectorControls>
				<PanelBody title="Параметри відображення" initialOpen={ true }>
					<ToggleControl
						label="Показувати назви соцмереж поруч із іконками"
						checked={ globalShow }
						onChange={ (v)=> setAttributes({ showLabels: !!v }) }
					/>
				</PanelBody>
			</InspectorControls>

			<div className={ "ntd-social-links" + (globalShow ? " ntd-social-links--labels" : "") } ref={ anchorRef }>
				<HStack className="ntd-social-links__row" spacing="2rem">
					{ links.map( (item, index) => {
						const Ico   = ICONS[item.type] || IconWebsite;
						const title = PLATFORMS.find(p=>p.type===item.type)?.label || item.type;
						const showLabel = globalShow && (item.showLabel ?? true);
						return (
							<div key={ item.type } className="ntd-social-links__item">
								<Tooltip text={ title }>
									<Button
										className={ showLabel ? "ntd-social-links__btn has-label" : "ntd-social-links__btn" }
										onClick={ ()=>setEditingIndex(index) }
										aria-label={ title }
									>
										<Ico/>
										{ showLabel && <span className="ntd-social-links__label">{ title }</span> }
									</Button>
								</Tooltip>

								{ editingIndex === index && (
									<Popover
										anchor={ anchorRef.current }
										placement="bottom-start"
										onClose={ ()=>setEditingIndex(null) }
										className="ntd-social-links__popover"
									>
										<div className="ntd-social-links__pop-inner">
											<strong className="ntd-social-links__pop-title">{ title }</strong>

											<Spacer margin="8px 0 0">
												<TextControl
													label="URL"
													placeholder="https://..."
													value={ item.url ?? '' }
													onChange={ (v)=>setUrlAt(index,v) }
												/>
											</Spacer>

											<Spacer margin="8px 0 0">
												<ToggleControl
													label="Показувати назву поруч із іконкою (для цього посилання)"
													checked={ item.showLabel ?? true }
													onChange={ (v)=> setShowLabelAt(index, v) }
													help="Працює лише якщо глобально увімкнено показ назв у параметрах блоку."
												/>
											</Spacer>

											<Spacer margin="8px 0 0">
												<div className="ntd-social-links__pop-actions">
													<Button variant="secondary" onClick={ ()=>setEditingIndex(null) }>Готово</Button>
													<Button isDestructive variant="tertiary" onClick={ ()=>removeAt(index) }>Видалити</Button>
												</div>
											</Spacer>
										</div>
									</Popover>
								) }
							</div>
						);
					}) }

					<Button
						icon={ plus }
						label="Додати"
						className="ntd-social-links__add"
						onClick={ ()=>setPickerOpen((v)=>!v) }
						aria-expanded={ pickerOpen }
					/>

					{ pickerOpen && (
						<Popover
							anchor={ anchorRef.current }
							placement="bottom-start"
							onClose={ ()=>setPickerOpen(false) }
							className="ntd-social-links__picker"
						>
							<div className="ntd-social-links__grid">
								{ available.length ? available.map( (p) => {
									const Ico = ICONS[p.type] || IconWebsite;
									return (
										<button
											type="button"
											key={ p.type }
											className="ntd-social-links__grid-item"
											onClick={ ()=>addPlatform(p.type) }
										>
											<span className="ntd-social-links__grid-icon"><Ico/></span>
											<span className="ntd-social-links__grid-label">{ p.label }</span>
										</button>
									);
								}) : (
									<div className="ntd-social-links__grid-empty">Усе додано</div>
								) }
							</div>
						</Popover>
					)}
				</HStack>
			</div>
		</>
	);
}

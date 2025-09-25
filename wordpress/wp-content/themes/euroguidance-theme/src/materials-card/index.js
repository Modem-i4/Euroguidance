import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	MediaUpload,
	MediaUploadCheck
} from '@wordpress/block-editor';
import {
	Button,
	TextControl
} from '@wordpress/components';
import { trash } from '@wordpress/icons';
import metadata from './block.json';
import './style.scss'; 
 
registerBlockType(metadata.name, {
	edit: ({ attributes, setAttributes, isSelected, onReplace }) => {
		const { title, file, imageUrl } = attributes;

		const onSelectImage = (media) => {
			setAttributes({ imageUrl: media.url });
		};

		const onSelectFile = (media) => {
			setAttributes({ file: media.url });
		};

		const confirmDelete = () => {
			if (window.confirm('Ви справді хочете видалити цей блок?')) {
				onReplace([]);
			}
		};

		return (
			<div {...useBlockProps({ className: 'file-card file-card-editor' })}>
				<MediaUploadCheck>
					<MediaUpload
						onSelect={onSelectImage}
						allowedTypes={['image']}
						render={({ open }) => (
							<>
								{imageUrl ? (
									<img src={imageUrl} className="thumbnail" alt="Image"  onClick={open}/>
								) : (
									<div className='thumbnail-wrapper'>
										<div className="thumbnail" onClick={open}>🖼️+</div>
									</div>
								)}
							</>
						)}
					/>
				</MediaUploadCheck>

				<TextControl
					placeholder="Назва"
					value={title}
					onChange={(val) => setAttributes({ title: val })}
				/>

				<MediaUploadCheck>
					<MediaUpload
						onSelect={onSelectFile}
						allowedTypes={['application/pdf', 'application/msword']}
						render={({ open }) => (
							<Button onClick={open} variant="secondary" className='icon-button'>
								🔗
							</Button>
						)}
					/>
				</MediaUploadCheck>
				<Button
					onClick={confirmDelete}
					variant="secondary"
					className="icon-button close-icon"
					aria-label="Видалити файл"
					title="Видалити файл"
				>
					❌
				</Button>	
			</div>
		);
	},

	save: ({ attributes }) => {
	const { title, imageUrl, file } = attributes;

	if (!file) return null;

	return (
		<a href={file} target="_blank" rel="noopener noreferrer" className="file-card">
			<img className="thumbnail" src={imageUrl ? imageUrl : '/wp-content/uploads/2025/08/tools-resume.svg'} alt="" />
			{title && <div className="file-title">{title}</div>}
		</a>
	);
},


});

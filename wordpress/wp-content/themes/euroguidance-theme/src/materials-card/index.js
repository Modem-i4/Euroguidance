import { registerBlockType } from '@wordpress/blocks';
import {
  useBlockProps,
  MediaUpload,
  MediaUploadCheck
} from '@wordpress/block-editor';
import {
  Button,
  TextControl,
  Modal
} from '@wordpress/components';
import metadata from './block.json';
import './style.scss';

registerBlockType(metadata.name, {
  edit: ({ attributes, setAttributes, onReplace }) => {
    const { title, file, imageUrl, activeAttachment } = attributes;

    // IMAGE — без змін
    const onSelectImage = (media) => {
      setAttributes({ imageUrl: media?.url || '' });
    };

    // FILE (📄) — MediaUpload; це головний href
    const onSelectFile = (media) => {
      setAttributes({ file: media?.url || '', activeAttachment: 'file' });
    };

    // LINK (🔗) — модалка
    const [isLinkModalOpen, setLinkModalOpen] = wp.element.useState(false);
    const [tempLink, setTempLink] = wp.element.useState(file || '');

    const openLinkModal = () => {
      setAttributes({ activeAttachment: 'link' });
      setTempLink(file || '');
      setLinkModalOpen(true);
    };

    const saveLink = () => {
      setAttributes({ file: (tempLink || '').trim(), activeAttachment: 'link' });
      setLinkModalOpen(false);
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
					<div className="thumbnail-wrapper">
						<button
						type="button"
						className="thumbnail-button"
						onClick={open}
						aria-label="Змінити зображення"
						title="Змінити зображення"
						>
						<img src={imageUrl} className="thumbnail" alt="Image" />
						</button>
					</div>
					) : (
					<div className="thumbnail-wrapper">
						<button
						type="button"
						className="thumbnail-button"
						onClick={open}
						aria-label="Додати зображення"
						title="Додати зображення"
						>
						<div className="thumbnail">🖼️+</div>
						</button>
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

        {/* 📄 — вибір файлу через MediaUpload */}
        <MediaUploadCheck>
          <MediaUpload
            onSelect={onSelectFile}
            allowedTypes={[
              'application/pdf',
              'application/msword',
              'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
              'application/vnd.ms-excel',
              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              'application/zip'
            ]}
            render={({ open }) => (
              <Button
                onClick={open}
                variant="secondary"
                className={`icon-button ${activeAttachment === 'file' ? 'is-active' : ''}`}
                aria-label="Обрати файл"
                title="Обрати файл"
              >
                📄
              </Button>
            )}
          />
        </MediaUploadCheck>

        {/* 🔗 — додати посилання (модалка) */}
        <Button
          onClick={openLinkModal}
          variant="secondary"
          className={`icon-button ${activeAttachment === 'link' ? 'is-active' : ''}`}
          aria-label="Додати посилання"
          title="Додати посилання"
        >
          🔗
        </Button>

        {/* видалення блоку — без змін */}
        <Button
          onClick={confirmDelete}
          variant="secondary"
          className="icon-button close-icon"
          aria-label="Видалити файл"
          title="Видалити файл"
        >
          ❌
        </Button>

        {isLinkModalOpen && (
          <Modal
            title="Додати посилання"
            onRequestClose={() => setLinkModalOpen(false)}
            shouldCloseOnClickOutside={true}
          >
            <TextControl
              label="URL"
              placeholder="https://..."
              value={tempLink}
              onChange={setTempLink}
            />
            <div style={{ display: 'flex', gap: '8px', justifyContent: 'flex-end', marginTop: '12px' }}>
              <Button onClick={() => setLinkModalOpen(false)} variant="secondary">Скасувати</Button>
              <Button onClick={saveLink} variant="primary">Зберегти</Button>
            </div>
          </Modal>
        )}
      </div>
    );
  },

  save: ({ attributes }) => {
    const { title, imageUrl, file } = attributes;

    if (!file) return null;

    return (
      <a href={file} target="_blank" rel="noopener noreferrer" className="file-card">
        <img className="thumbnail"
             src={imageUrl ? imageUrl : '/wp-content/uploads/2025/08/tools-resume.svg'}
             alt="" />
        {title && <div className="file-title">{title}</div>}
      </a>
    );
  },
});

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, TextControl, Modal } from '@wordpress/components';
import metadata from './block.json';
import './style.scss';

function ensureScheme(url){
  if (!url) return '';
  return /^https?:\/\//i.test(url) ? url.trim() : ('https://' + url.trim());
}
function genUid(){ return 'mrc-' + Math.random().toString(36).slice(2) + Date.now().toString(36); }

registerBlockType(metadata.name, {
  edit: ({ attributes, setAttributes, onReplace }) => {
    const { title, file, fileId, imageUrl, activeAttachment, uid } = attributes;

    // гарантуємо стабільний UID блока
    wp.element.useEffect(() => {
      if (!uid) setAttributes({ uid: genUid() });
    }, []);

    // IMAGE
    const onSelectImage = (media) => setAttributes({ imageUrl: media?.url || '' });

    // FILE (📄) — запам'ятовуємо і URL, і ID вкладення
    const onSelectFile = (media) => {
      setAttributes({ file: media?.url || '', fileId: media?.id || 0, activeAttachment: 'file' });
    };

    // LINK (🔗)
    const [isLinkModalOpen, setLinkModalOpen] = wp.element.useState(false);
    const [tempLink, setTempLink] = wp.element.useState(file || '');
    const openLinkModal = () => { setAttributes({ activeAttachment: 'link' }); setTempLink(file || ''); setLinkModalOpen(true); };
    const saveLink = () => {
      const url = ensureScheme(tempLink);
      setAttributes({ file: url, fileId: 0, activeAttachment: 'link' });
      setLinkModalOpen(false);
    };

    const confirmDelete = () => { if (window.confirm('Видалити цей блок?')) onReplace([]); };

    return (
      <div {...useBlockProps({ className: 'file-card file-card-editor' })}>
        <MediaUploadCheck>
          <MediaUpload onSelect={onSelectImage} allowedTypes={['image']} render={({ open }) => (
            <>
              {imageUrl ? (
                <div className="thumbnail-wrapper">
                  <button type="button" className="thumbnail-button" onClick={open} aria-label="Змінити зображення" title="Змінити зображення">
                    <img src={imageUrl} className="thumbnail" alt="Image" />
                  </button>
                </div>
              ) : (
                <div className="thumbnail-wrapper">
                  <button type="button" className="thumbnail-button" onClick={open} aria-label="Додати зображення" title="Додати зображення">
                    <div className="thumbnail">🖼️+</div>
                  </button>
                </div>
              )}
            </>
          )} />
        </MediaUploadCheck>

        <TextControl placeholder="Назва" value={title} onChange={(val) => setAttributes({ title: val })} />

        <MediaUploadCheck>
          <MediaUpload
            onSelect={onSelectFile}
            allowedTypes={[
              'application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
              'application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/zip'
            ]}
            render={({ open }) => (
              <Button onClick={open} variant="secondary" className={`icon-button ${activeAttachment === 'file' ? 'is-active' : ''}`} aria-label="Обрати файл" title="Обрати файл">📄</Button>
            )}
          />
        </MediaUploadCheck>

        <Button onClick={openLinkModal} variant="secondary" className={`icon-button ${activeAttachment === 'link' ? 'is-active' : ''}`} aria-label="Додати посилання" title="Додати посилання">🔗</Button>

        <Button onClick={confirmDelete} variant="secondary" className="icon-button close-icon" aria-label="Видалити файл" title="Видалити файл">❌</Button>

        {isLinkModalOpen && (
          <Modal title="Додати посилання" onRequestClose={() => setLinkModalOpen(false)} shouldCloseOnClickOutside>
            <TextControl label="URL" placeholder="https://..." value={tempLink} onChange={setTempLink} />
            <div style={{ display:'flex', gap:'8px', justifyContent:'flex-end', marginTop:'12px' }}>
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
        <img className="thumbnail" src={imageUrl ? imageUrl : '/wp-content/uploads/2025/08/tools-resume.svg'} alt="" />
        {title && <div className="file-title">{title}</div>}
      </a>
    );
  },
});

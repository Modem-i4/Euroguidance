import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, Button, RangeControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes, context }) {
  const { fallbackImage = '', excerptLength = 200 } = attributes;
  const { postId, postType } = context || {};
  const blockProps = useBlockProps({ className: 'news-latest-card' });

  // пост, медіа і категорії
  const { post, media, cats } = useSelect((select) => {
    if (!postId || !postType) return { post: null, media: null, cats: [] };
    const core = select('core');
    const p = core.getEntityRecord('postType', postType, postId);
    const m = p?.featured_media ? core.getMedia(p.featured_media) : null;
    const c = p?.categories?.length ? core.getEntityRecords('taxonomy', 'category', { include: p.categories }) : [];
    return { post: p, media: m, cats: c || [] };
  }, [postId, postType]);

  // формат дати: DD/MM/YYYY
  const formatDate = (iso) => {
    if (!iso) return '';
    const d = new Date(iso);
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
  };

  const stripHtml = (html = '') => html.replace(/<[^>]*>/g, '').trim();
  const trimWords = (text, words = 200) => {
    const parts = text.split(/\s+/);
    if (parts.length <= words) return text;
    return parts.slice(0, words).join(' ') + ' […]';
  };

  const title = post?.title?.rendered ? stripHtml(post.title.rendered) : '';
  const link = post?.link || '#';
  const img = media?.source_url || (fallbackImage || '');
  const pubDate = formatDate(post?.date_gmt || post?.date);

  const rawExcerpt = post?.excerpt?.rendered ? stripHtml(post.excerpt.rendered) : '';
  const editorExcerpt = trimWords(rawExcerpt, excerptLength);

  const firstCat = cats && cats.length ? cats[0]?.name : '';

  return (
    <>
      <InspectorControls>
        <PanelBody title="Налаштування картки" initialOpen={true}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={(m) => setAttributes({ fallbackImage: m?.url || '' })}
              allowedTypes={['image']}
              render={({ open }) => (
                <Button variant="secondary" onClick={open}>
                  {fallbackImage ? 'Змінити fallback-зображення' : 'Обрати fallback-зображення'}
                </Button>
              )}
            />
          </MediaUploadCheck>
          {!!fallbackImage && (
            <Button variant="link" onClick={() => setAttributes({ fallbackImage: '' })} style={{ marginTop: 8 }}>
              Прибрати fallback
            </Button>
          )}
          <RangeControl
            label="Довжина уривка (слова)"
            value={excerptLength}
            onChange={(value) => setAttributes({ excerptLength: value })}
            min={10}
            max={600}
            step={10}
          />
        </PanelBody>
      </InspectorControls>

      {post ? (
        <div {...blockProps}>
          <a className="card-wrapper" href={link} onClick={(e) => e.preventDefault()}>
            <div className="card-image-wrapper">
              {img ? <img src={img} alt={title || ''} className="card-image" /> : <div className="card-image-placeholder" />}
            </div>

            {/* без лінії/градієнт-бара */}

            <div className="card-content">
              <div className="card-meta-top">
                <div className="card-date">{pubDate}</div>
                {firstCat ? (
                  <span className="badge-category">{firstCat}</span>
                ) : (
                  <span className="badge-category is-empty"> </span>
                )}
              </div>

              <h3 className="card-title">{title || 'Без назви'}</h3>

              <div className="card-excerpt">{editorExcerpt}</div>
            </div>
          </a>
        </div>
      ) : null}
    </>
  );
}

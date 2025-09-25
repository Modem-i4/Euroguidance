import {
  InspectorControls,
  useBlockProps,
  MediaUpload,
  MediaUploadCheck,
  store as blockEditorStore,
} from '@wordpress/block-editor';
import {
  PanelBody,
  RangeControl,
  Spinner,
  Button,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useRef } from '@wordpress/element';
import { initNewsSlider } from './script.js';

export default function Edit({ attributes, setAttributes, clientId }) {
  const { postsToShow = 6, slidesPerView = 3, slidesGapPx = 25, fallbackImage = '' } = attributes;

  const posts = useSelect(
    (select) =>
      select('core').getEntityRecords('postType', 'post', {
        per_page: postsToShow,
        _embed: true,
        order: 'desc',
        orderby: 'date',
      }),
    [postsToShow]
  );

  const containerRef = useRef(null);

  const blockProps = useBlockProps({
    className: 'swiper-container preview-mode',
    tabIndex: 0,
  });

  const { selectBlock } = useDispatch(blockEditorStore);
  const handleSelectCapture = () => selectBlock(clientId);

  useEffect(() => {
    if (!containerRef.current) return;
    if (!posts || !posts.length) return;

    const sliderEl = containerRef.current.querySelector('.news-slider.swiper');
    if (sliderEl?.swiper) sliderEl.swiper.destroy(true, true);

    setTimeout(() => {
      initNewsSlider(containerRef.current, { isEditor: true });
    }, 0);
  }, [posts, slidesPerView, slidesGapPx]);

  return (
    <>
      <InspectorControls>
        <PanelBody title="Налаштування блоку">
          <RangeControl
            label="Кількість новин"
            value={postsToShow}
            onChange={(val) => setAttributes({ postsToShow: val })}
            min={3}
            max={20}
          />
          <RangeControl
            label="Кількість слайдів на екрані"
            value={slidesPerView}
            onChange={(val) => setAttributes({ slidesPerView: val })}
            min={1}
            max={3}
          />
          <RangeControl
            label="Додаткова відстань між слайдами (px)"
            value={slidesGapPx}
            onChange={(val) => setAttributes({ slidesGapPx: val })}
            min={0}
            max={90}
          />
        </PanelBody>

        <PanelBody title="Запасне зображення" initialOpen={false}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={(media) => setAttributes({ fallbackImage: media.url })}
              allowedTypes={['image']}
              value={fallbackImage}
              render={({ open }) => (
                <>
                  {fallbackImage && (
                    <img
                      src={fallbackImage}
                      alt="Fallback"
                      style={{ maxWidth: '100%', marginBottom: 10, borderRadius: 8 }}
                    />
                  )}
                  <div style={{ display: 'flex', gap: 8 }}>
                    <Button onClick={open} isSecondary>
                      {fallbackImage ? 'Змінити fallback' : 'Обрати fallback'}
                    </Button>
                    {fallbackImage && (
                      <Button
                        isDestructive
                        onClick={() => setAttributes({ fallbackImage: '' })}
                      >
                        Прибрати
                      </Button>
                    )}
                  </div>
                </>
              )}
            />
          </MediaUploadCheck>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps} ref={containerRef} onMouseDownCapture={handleSelectCapture}>
        <div
          className={`news-slider swiper is-editor slides-${slidesPerView}`}
          data-slides-per-view={slidesPerView}
          data-slides-gap-px={slidesGapPx}
        >
          <div className="swiper-wrapper">
            {!posts && <Spinner />}
            {posts &&
              posts.map((post) => {
                const title = post.title?.rendered ?? '';
                const image =
                  post._embedded?.['wp:featuredmedia']?.[0]?.media_details?.sizes?.medium?.source_url ||
                  '';

                return (
                  <div key={post.id} className="news-latest-card swiper-slide">
                    <div class="card-wrapper">
                      <div className="card-image-wrapper">
                        {image ? (
                          <img src={image} alt={title} className="card-image" />
                        ) : fallbackImage ? (
                          <img src={fallbackImage} alt={title} className="card-image" />
                        ) : (
                          <div className="card-image-placeholder" />
                        )}
                      </div>

                      <div className="card-gradient-bar" />

                      <div className="card-content">
                        <h3 className="card-title" dangerouslySetInnerHTML={{ __html: title }} />
                        <div className="card-meta">
                          <div className="card-type">Публікація</div>
                          <div className="card-date">{humanReadableDiff(post.date)}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                );
              })}
          </div>
        </div>

        <div className="swiper-button-prev"></div>
        <div className="swiper-button-next"></div>
        <div className="swiper-pagination"></div>
      </div>
    </>
  );
}

function humanReadableDiff(dateISO) {
  const now = new Date();
  const then = new Date(dateISO);
  const seconds = Math.floor((now - then) / 1000);
  const intervals = [
    { label: 'р.', seconds: 31536000 },
    { label: 'міс.', seconds: 2592000 },
    { label: 'тиж.', seconds: 604800 },
    { label: 'дн.', seconds: 86400 },
    { label: 'год', seconds: 3600 },
    { label: 'хв.', seconds: 60 },
  ];
  for (const { label, seconds: s } of intervals) {
    const count = Math.floor(seconds / s);
    if (count >= 1) return `${count} ${label} тому`;
  }
  return 'щойно';
}

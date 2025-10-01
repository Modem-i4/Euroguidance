// edit.js
import { __ } from '@wordpress/i18n';
import {
  MediaUpload,
  MediaUploadCheck,
  InspectorControls,
  BlockControls,
  useBlockProps
} from '@wordpress/block-editor';
import {
  PanelBody,
  ToggleControl,
  Button,
  TextControl,
  ToolbarGroup,
  ToolbarButton
} from '@wordpress/components';
import { Fragment, useState } from '@wordpress/element';
import { plus, trash, chevronLeft, chevronRight } from '@wordpress/icons';

export default function Edit({ attributes, setAttributes }) {
  const { slides = [], showArrows, showDots } = attributes;
  const [selectedIndex, setSelectedIndex] = useState(null);
  const [hoverIndex, setHoverIndex] = useState(null);

  const blockProps = useBlockProps({
    className: 'ntd-logo-carousel ntd-logo-carousel--editor'
  });

  const mediaAlt = (media) =>
    media?.alt ||
    media?.alt_text ||
    media?.title ||
    media?.name ||
    media?.filename ||
    '';

  const addSlide = (media) => {
    if (!media?.url) return;
    const next = [
      ...slides,
      {
        id: media.id || null,
        url: media.url,
        alt: mediaAlt(media),
        width: media.width || null,
        height: media.height || null,
        href: '',
        target: '_blank',
        rel: ''
      }
    ];
    setAttributes({ slides: next });
    setSelectedIndex(next.length - 1);
  };

  const removeSlide = (idx) => {
    const next = slides.filter((_, i) => i !== idx);
    setAttributes({ slides: next });
    setSelectedIndex(null);
  };

  const move = (from, to) => {
    if (to < 0 || to >= slides.length) return;
    const next = [...slides];
    const [it] = next.splice(from, 1);
    next.splice(to, 0, it);
    setAttributes({ slides: next });
    setSelectedIndex(to);
  };

  const updateSlide = (idx, patch) => {
    const next = slides.map((s, i) => (i === idx ? { ...s, ...patch } : s));
    setAttributes({ slides: next });
  };

  return (
    <Fragment>
      <InspectorControls>
        <PanelBody title={__('Налаштування', 'ntd')}>
          <ToggleControl
            label={__('Показати стрілки', 'ntd')}
            checked={!!showArrows}
            onChange={(v) => setAttributes({ showArrows: v })}
          />
          <ToggleControl
            label={__('Показати точки', 'ntd')}
            checked={!!showDots}
            onChange={(v) => setAttributes({ showDots: v })}
          />
        </PanelBody>

        {Number.isInteger(selectedIndex) && slides[selectedIndex] && (
          <PanelBody title={__('Вибраний слайд', 'ntd')} initialOpen={true}>
            <TextControl
              label="alt"
              value={slides[selectedIndex].alt || ''}
              onChange={(v) => updateSlide(selectedIndex, { alt: v })}
            />
            <TextControl
              label="target"
              value={slides[selectedIndex].target || '_blank'}
              onChange={(v) => updateSlide(selectedIndex, { target: v })}
            />
            <TextControl
              label="rel"
              value={slides[selectedIndex].rel || ''}
              onChange={(v) => updateSlide(selectedIndex, { rel: v })}
            />
          </PanelBody>
        )}
      </InspectorControls>

      <BlockControls>
        <ToolbarGroup>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={addSlide}
              multiple={false}
              allowedTypes={['image']}
              render={({ open }) => (
                <ToolbarButton icon={plus} onClick={open}>
                  {__('Додати слайд', 'ntd')}
                </ToolbarButton>
              )}
            />
          </MediaUploadCheck>
        </ToolbarGroup>
      </BlockControls>

      <div {...blockProps}>
        <div className="ntd-logo-carousel__viewport">
          <div className="ntd-logo-carousel__track">
            {slides.length === 0 && (
              <div className="ntd-logo-carousel__empty">
                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={addSlide}
                    allowedTypes={['image']}
                    render={({ open }) => (
                      <Button variant="primary" onClick={open}>
                        {__('Додати перший слайд', 'ntd')}
                      </Button>
                    )}
                  />
                </MediaUploadCheck>
              </div>
            )}

            {slides.map((s, i) => (
              <div
                key={i}
                className={
                  'ntd-logo-carousel__slide' +
                  (selectedIndex === i ? ' is-selected' : '')
                }
                onClick={() => setSelectedIndex(i)}
              >
                <div className="ntd-logo-carousel__bar">
                  <Button
                    icon={chevronLeft}
                    onClick={(e) => {
                      e.stopPropagation();
                      move(i, i - 1);
                    }}
                    label={__('Ліворуч', 'ntd')}
                  />
                  <Button
                    icon={trash}
                    variant="secondary"
                    isDestructive
                    onClick={(e) => {
                      e.stopPropagation();
                      removeSlide(i);
                    }}
                    label={__('Видалити', 'ntd')}
                  />
                  <Button
                    icon={chevronRight}
                    onClick={(e) => {
                      e.stopPropagation();
                      move(i, i + 1);
                    }}
                    label={__('Праворуч', 'ntd')}
                  />
                </div>

                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={(media) =>
                      updateSlide(i, {
                        id: media.id || null,
                        url: media.url,
                        alt:
                          slides[i]?.alt && slides[i].alt.trim() !== ''
                            ? slides[i].alt
                            : mediaAlt(media),
                        width: media.width || null,
                        height: media.height || null
                      })
                    }
                    allowedTypes={['image']}
                    render={({ open }) => (
                      <div
                        className="ntd-logo-carousel__thumb"
                        onClick={(e) => {
                          e.stopPropagation();
                          open();
                        }}
                        onMouseEnter={() => setHoverIndex(i)}
                        onMouseLeave={() => setHoverIndex(null)}
                        title={__('Натисніть, щоб змінити зображення', 'ntd')}
                      >
                        {s?.url ? (
                          <img
                            src={s.url}
                            alt={s.alt || ''}
                            style={{
                              maxHeight: '96px',
                              opacity: hoverIndex === i ? 0.88 : 1,
                              outline:
                                hoverIndex === i ? '1px dashed #bdbdbd' : 'none',
                              outlineOffset: '2px',
                              transition: 'opacity .15s ease'
                            }}
                          />
                        ) : (
                          <div className="ntd-logo-carousel__placeholder" />
                        )}
                      </div>
                    )}
                  />
                </MediaUploadCheck>

                <div className="ntd-logo-carousel__fields">
                  <TextControl
                    label={__('Посилання', 'ntd')}
                    value={s.href || ''}
                    placeholder="https://..."
                    onChange={(v) => updateSlide(i, { href: v })}
                  />
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="ntd-logo-carousel__toolbar">
          <MediaUploadCheck>
            <MediaUpload
              onSelect={addSlide}
              multiple={false}
              allowedTypes={['image']}
              render={({ open }) => (
                <Button variant="secondary" onClick={open} icon={plus}>
                  {__('Додати слайд', 'ntd')}
                </Button>
              )}
            />
          </MediaUploadCheck>
        </div>
      </div>
    </Fragment>
  );
}

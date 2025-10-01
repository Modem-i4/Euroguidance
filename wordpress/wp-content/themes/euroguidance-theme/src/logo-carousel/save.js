// save.js
import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const { slides = [], showArrows, showDots } = attributes;

  const blockProps = useBlockProps.save({
    className: 'ntd-logo-carousel'
  });

  return (
    <div {...blockProps} data-has-dots={showDots} data-has-arrows={showArrows}>
      <div className="ntd-logo-carousel__viewport">
        <div className="ntd-logo-carousel__track">
          {slides.map((s, i) => {
            const img = (
              <img
                src={s.url}
                alt={s.alt || ''}
                width={s.width || undefined}
                height={s.height || undefined}
                loading="lazy"
                decoding="async"
              />
            );
            return (
              <div className="ntd-logo-carousel__slide" key={i}>
                {s.href ? (
                  <a
                    href={s.href}
                    target={s.target || '_blank'}
                    rel={s.rel || undefined}
                    className="ntd-logo-carousel__link"
                  >
                    {img}
                  </a>
                ) : (
                  img
                )}
              </div>
            );
          })}
        </div>
      </div>

      <div className="ntd-logo-carousel__controls">
        {showArrows && (
          <button className="ntd-logo-carousel__nav is-prev" type="button" aria-label="Попередня сторінка" />
        )}
        {showDots && <div className="ntd-logo-carousel__dots" aria-hidden="true" />}
        {showArrows && (
          <button className="ntd-logo-carousel__nav is-next" type="button" aria-label="Наступна сторінка" />
        )}
      </div>
    </div>
  );
}

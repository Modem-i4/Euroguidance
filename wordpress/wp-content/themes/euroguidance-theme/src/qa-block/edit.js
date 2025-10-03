import { useState, useCallback } from '@wordpress/element';
import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function Edit({ attributes, setAttributes }) {
  const { question, answer } = attributes;

  const [isOpen, setIsOpen] = useState(false);
  const onToggle = useCallback(() => setIsOpen((v) => !v), []);

  const blockProps = useBlockProps({
    className: `qa-item ${isOpen ? 'is-open' : ''}`,
  });

  return (
    <div {...blockProps}>
      <button
        type="button"
        className="qa-summary"
        aria-expanded={isOpen ? 'true' : 'false'}
        onClick={onToggle}
      >
        <span className="qa-arrow" aria-hidden="true" />
        <RichText
          tagName="span"
          className="qa-question"
          placeholder="Введіть питання…"
          value={question}
          onChange={(val) => setAttributes({ question: val })}
          allowedFormats={[]}
          onClick={(e) => e.stopPropagation()}
        />
      </button>

      <div className="qa-content" hidden={!isOpen}>
        <div className="qa-content-inner">
          <RichText
            tagName="div"
            className="qa-answer"
            placeholder="Напишіть відповідь…"
            value={answer}
            onChange={(val) => setAttributes({ answer: val })}
            onClick={(e) => e.stopPropagation()}
          />
        </div>
      </div>
    </div>
  );
}

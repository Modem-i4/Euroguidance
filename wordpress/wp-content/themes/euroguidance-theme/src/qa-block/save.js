import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const { question, answer } = attributes;

  return (
    <div
      {...useBlockProps.save({
        className: 'qa-item',
        'aria-expanded': 'false'
      })}
    >
      <button type="button" className="qa-summary">
        <span className="qa-arrow" aria-hidden="true" />
        <RichText.Content tagName="span" className="qa-question" value={question} />
      </button>

      <div className="qa-content">
        <div className="qa-content-inner">
          <RichText.Content tagName="div" value={answer} className="qa-answer" />
        </div>
      </div>
    </div>
  );
}

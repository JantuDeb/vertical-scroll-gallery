const { registerBlockType } = wp.blocks;
const { InspectorControls, RichText } = wp.blockEditor; // Using RichText for inline editing if needed, otherwise TextControl for Inspector
const { TextControl, PanelBody, TextareaControl } = wp.components; // TextareaControl for multi-line text in inspector if needed

registerBlockType('vsg/contact-form', {
    title: 'VSG Contact Form',
    icon: 'email', // WordPress Dashicon
    category: 'widgets', // Or 'common', 'layout', etc.
    attributes: {
        nameLabel: {
            type: 'string',
            default: 'Name',
        },
        emailLabel: {
            type: 'string',
            default: 'Email',
        },
        commentLabel: {
            type: 'string',
            default: 'Comment',
        },
        submitButtonText: {
            type: 'string',
            default: 'Submit',
        },
    },
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { nameLabel, emailLabel, commentLabel, submitButtonText } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Form Labels" initialOpen={true}>
                        <TextControl
                            label="Name Field Label"
                            value={nameLabel}
                            onChange={(value) => setAttributes({ nameLabel: value })}
                        />
                        <TextControl
                            label="Email Field Label"
                            value={emailLabel}
                            onChange={(value) => setAttributes({ emailLabel: value })}
                        />
                        <TextControl
                            label="Comment Field Label"
                            value={commentLabel}
                            onChange={(value) => setAttributes({ commentLabel: value })}
                        />
                        <TextControl
                            label="Submit Button Text"
                            value={submitButtonText}
                            onChange={(value) => setAttributes({ submitButtonText: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="vsg-contact-form-preview">
                    <form>
                        <div>
                            <label>{nameLabel}</label>
                            <input type="text" readOnly disabled />
                        </div>
                        <div>
                            <label>{emailLabel}</label>
                            <input type="email" readOnly disabled />
                        </div>
                        <div>
                            <label>{commentLabel}</label>
                            <textarea readOnly disabled></textarea>
                        </div>
                        <button type="button" disabled>{submitButtonText}</button>
                    </form>
                </div>
            </>
        );
    },
    save: (props) => {
        const { attributes } = props;
        const { nameLabel, emailLabel, commentLabel, submitButtonText } = attributes;

        return (
            <form className="vsg-contact-form vsg-contact-submission-form">
                <div>
                    <label htmlFor="vsg_name">{nameLabel}</label>
                    <input type="text" name="vsg_name" id="vsg_name" />
                </div>
                <div>
                    <label htmlFor="vsg_email">{emailLabel}</label>
                    <input type="email" name="vsg_email" id="vsg_email" />
                </div>
                <div>
                    <label htmlFor="vsg_comment">{commentLabel}</label>
                    <textarea name="vsg_comment" id="vsg_comment"></textarea>
                </div>
                <button type="submit" data-submit-text={submitButtonText}>
                    {submitButtonText}
                </button>
                <div className="vsg-form-message"></div>
            </form>
        );
    },
});

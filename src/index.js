import { registerBlockVariation } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import './style.scss';

addFilter('blocks.registerBlockType', 'vertical-scroll-gallery/attributes', (settings, name) => {
    if (name !== 'core/gallery') return settings;
    return { ...settings, attributes: { ...settings.attributes, displayMode: {
        type: 'string', enum: ['inherit', 'default', 'scroll', 'individual'], default: 'inherit',
    } } };
});

const effectiveMode = (attributes) => {
    const mode = attributes.displayMode || 'inherit';
    if (mode !== 'inherit') return mode;
    if ((attributes.className || '').split(/\s+/).includes('is-style-vertical-scroll-gallery')) return 'scroll';
    return window.vsgEditorSettings?.mode || 'default';
};

addFilter('editor.BlockEdit', 'vertical-scroll-gallery/controls', (BlockEdit) => (props) => {
    if (props.name !== 'core/gallery') return <BlockEdit {...props} />;
    const mode = effectiveMode(props.attributes);
    return <>
        <BlockEdit {...props} />
        <InspectorControls>
            <PanelBody title={__('Gallery layout', 'vertical-scroll-gallery')}>
                <SelectControl
                    label={__('Display mode', 'vertical-scroll-gallery')}
                    value={props.attributes.displayMode || 'inherit'}
                    options={[
                        { label: __('Use site setting', 'vertical-scroll-gallery'), value: 'inherit' },
                        { label: __('Scrollable pages', 'vertical-scroll-gallery'), value: 'scroll' },
                        { label: __('Full-size vertical images', 'vertical-scroll-gallery'), value: 'individual' },
                        { label: __('WordPress gallery', 'vertical-scroll-gallery'), value: 'default' },
                    ]}
                    onChange={(displayMode) => {
                        const updates = { displayMode };
                        if (displayMode === 'inherit') {
                            // Remove only the legacy override so site defaults can apply.
                            const classes = (props.attributes.className || '').split(/\s+/);
                            updates.className = classes.filter((name) =>
                                name && name !== 'is-style-vertical-scroll-gallery'
                            ).join(' ') || undefined;
                        }
                        props.setAttributes(updates);
                    }}
                    help={__('Choose WordPress gallery to keep the normal grid even when a site-wide override is enabled.', 'vertical-scroll-gallery')}
                />
                {mode !== 'default' && <Notice status="info" isDismissible={false}>
                    {__('Images keep their original proportions in one column. Columns and crop settings apply only to the WordPress layout. Scrolling is available on the published page; the editor shows the image stack.', 'vertical-scroll-gallery')}
                </Notice>}
            </PanelBody>
        </InspectorControls>
    </>;
});

// Editor-only wrapper attributes; saved core Gallery HTML stays untouched.
addFilter('editor.BlockListBlock', 'vertical-scroll-gallery/preview', (BlockListBlock) => (props) => {
    if (props.name !== 'core/gallery') return <BlockListBlock {...props} />;
    return <BlockListBlock {...props} wrapperProps={{ ...props.wrapperProps, 'data-vsg-mode': effectiveMode(props.attributes) }} />;
});

registerBlockVariation('core/gallery', {
    name: 'vertical-scroll-gallery',
    title: __('Vertical Scroll Image List', 'vertical-scroll-gallery'),
    icon: 'images-alt2',
    description: __('Read image pages in a compact, keyboard-accessible scroll area.', 'vertical-scroll-gallery'),
    attributes: { className: 'is-style-vertical-scroll-gallery', linkTo: 'none', columns: 1, displayMode: 'scroll' },
    scope: ['block', 'inserter', 'transform'],
    isActive: (attributes) => effectiveMode(attributes) === 'scroll',
});

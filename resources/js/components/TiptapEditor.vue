<template>
    <div class="tiptap-editor">
        <div class="tiptap-toolbar">
            <!-- Text formatting -->
            <button
                type="button"
                class="tiptap-toolbar-btn"
                :class="{ active: editor?.isActive('bold') }"
                @click="editor?.chain().focus().toggleBold().run()"
                title="Bold"
            >
                <strong>B</strong>
            </button>
            <button
                type="button"
                class="tiptap-toolbar-btn"
                :class="{ active: editor?.isActive('italic') }"
                @click="editor?.chain().focus().toggleItalic().run()"
                title="Italic"
            >
                <em>I</em>
            </button>
            <button
                type="button"
                class="tiptap-toolbar-btn"
                :class="{ active: editor?.isActive('underline') }"
                @click="editor?.chain().focus().toggleUnderline().run()"
                title="Underline"
            >
                <u>U</u>
            </button>
            <button
                type="button"
                class="tiptap-toolbar-btn"
                :class="{ active: editor?.isActive('strike') }"
                @click="editor?.chain().focus().toggleStrike().run()"
                title="Strikethrough"
            >
                <s>S</s>
            </button>
            <button
                type="button"
                class="tiptap-toolbar-btn"
                :class="{ active: editor?.isActive('subscript') }"
                @click="toggleSubscript"
                title="Subscript"
            >
                X<sub>2</sub>
            </button>
            <button
                type="button"
                class="tiptap-toolbar-btn"
                :class="{ active: editor?.isActive('superscript') }"
                @click="toggleSuperscript"
                title="Superscript"
            >
                X<sup>2</sup>
            </button>

            <span class="tiptap-toolbar-divider"></span>

            <!-- Color & Highlight -->
            <div class="tiptap-color-picker">
                <button
                    type="button"
                    class="tiptap-toolbar-btn"
                    :class="{ active: editor?.isActive('textStyle') }"
                    @click.stop="toggleDropdown('color')"
                    title="Text Color"
                >
                    <span class="color-icon" :style="{ borderBottomColor: currentColor || '#000' }">A</span>
                </button>
                <div v-if="showColorPicker" class="tiptap-color-dropdown" @click.stop>
                    <button
                        v-for="color in colors"
                        :key="color"
                        type="button"
                        class="color-option"
                        :style="{ backgroundColor: color }"
                        @click="setColor(color)"
                    ></button>
                    <button type="button" class="color-option color-reset" @click="setColor(null)">✕</button>
                </div>
            </div>
            <div class="tiptap-color-picker">
                <button
                    type="button"
                    class="tiptap-toolbar-btn"
                    :class="{ active: editor?.isActive('highlight') }"
                    @click.stop="toggleDropdown('highlight')"
                    title="Highlight"
                >
                    <span class="highlight-icon" :style="{ backgroundColor: currentHighlight || '#ffffba' }">H</span>
                </button>
                <div v-if="showHighlightPicker" class="tiptap-color-dropdown" @click.stop>
                    <button
                        v-for="color in highlightColors"
                        :key="color"
                        type="button"
                        class="color-option"
                        :style="{ backgroundColor: color }"
                        @click="setHighlight(color)"
                    ></button>
                    <button type="button" class="color-option color-reset" @click="setHighlight(null)">✕</button>
                </div>
            </div>

            <span class="tiptap-toolbar-divider"></span>

            <!-- Lists -->
            <button
                type="button"
                class="tiptap-toolbar-btn"
                :class="{ active: editor?.isActive('bulletList') }"
                @click="editor?.chain().focus().toggleBulletList().run()"
                title="Bullet List"
            >
                •
            </button>
            <button
                type="button"
                class="tiptap-toolbar-btn"
                :class="{ active: editor?.isActive('orderedList') }"
                @click="editor?.chain().focus().toggleOrderedList().run()"
                title="Numbered List"
            >
                1.
            </button>

            <span class="tiptap-toolbar-divider"></span>

            <!-- Link -->
            <button
                type="button"
                class="tiptap-toolbar-btn"
                :class="{ active: editor?.isActive('link') }"
                @click="openLinkModal"
                title="Link"
            >
                🔗
            </button>

            <!-- Image -->
            <button
                type="button"
                class="tiptap-toolbar-btn"
                @click="openImageModal"
                title="Image"
            >
                🖼
            </button>

            <!-- Video -->
            <button
                type="button"
                class="tiptap-toolbar-btn"
                @click="openVideoModal"
                title="Video"
            >
                ▶
            </button>

            <!-- Table -->
            <div class="tiptap-table-menu">
                <button
                    type="button"
                    class="tiptap-toolbar-btn"
                    :class="{ active: editor?.isActive('table') }"
                    @click.stop="toggleDropdown('table')"
                    title="Table"
                >
                    ▦
                </button>
                <div v-if="showTableMenu" class="tiptap-table-dropdown" @click.stop>
                    <button type="button" @click="insertTable">Insert Table</button>
                    <template v-if="editor?.isActive('table')">
                        <button type="button" @click="addColumnBefore">Add Column Before</button>
                        <button type="button" @click="addColumnAfter">Add Column After</button>
                        <button type="button" @click="deleteColumn">Delete Column</button>
                        <button type="button" @click="addRowBefore">Add Row Before</button>
                        <button type="button" @click="addRowAfter">Add Row After</button>
                        <button type="button" @click="deleteRow">Delete Row</button>
                        <button type="button" @click="deleteTable">Delete Table</button>
                    </template>
                </div>
            </div>

            <span class="tiptap-toolbar-divider"></span>

            <!-- Clear formatting -->
            <button
                type="button"
                class="tiptap-toolbar-btn"
                @click="editor?.chain().focus().unsetAllMarks().clearNodes().run()"
                title="Clear Formatting"
            >
                ✕
            </button>
        </div>
        <EditorContent :editor="editor" class="tiptap-content" />

        <!-- Modals teleported to body for full-page blocking -->
        <Teleport to="body">
            <!-- Link Modal -->
            <div v-if="showLinkModal" class="editor-modal-overlay" @click.self="closeLinkModal">
                <div class="editor-modal">
                    <button class="editor-modal-close" @click="closeLinkModal">×</button>
                    <h3 class="editor-modal-title">{{ editor?.isActive('link') ? 'Edit Link' : 'Insert Link' }}</h3>
                    <div class="editor-modal-field">
                        <label>URL</label>
                        <input
                            v-model="linkUrl"
                            type="url"
                            placeholder="https://example.com"
                            @keyup.enter="insertLink"
                        />
                    </div>
                    <div class="editor-modal-field">
                        <label>Label (optional)</label>
                        <input
                            v-model="linkLabel"
                            type="text"
                            placeholder="Link text"
                            @keyup.enter="insertLink"
                        />
                    </div>
                    <div class="editor-modal-actions">
                        <button v-if="editor?.isActive('link')" class="editor-modal-btn danger" @click="removeLink">Remove Link</button>
                        <button class="editor-modal-btn secondary" @click="closeLinkModal">Cancel</button>
                        <button class="editor-modal-btn primary" @click="insertLink" :disabled="!linkUrl">
                            {{ editor?.isActive('link') ? 'Update' : 'Insert' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Image Modal -->
            <div v-if="showImageModal" class="editor-modal-overlay" @click.self="closeImageModal">
                <div class="editor-modal">
                    <button class="editor-modal-close" @click="closeImageModal">×</button>
                    <h3 class="editor-modal-title">Insert Image</h3>
                    <div class="editor-modal-tabs">
                        <button
                            :class="{ active: imageTab === 'url' }"
                            @click="imageTab = 'url'"
                        >URL</button>
                        <button
                            :class="{ active: imageTab === 'upload' }"
                            @click="imageTab = 'upload'"
                        >Upload</button>
                    </div>
                    <div v-if="imageTab === 'url'" class="editor-modal-field">
                        <label>Image URL</label>
                        <input
                            v-model="imageUrl"
                            type="url"
                            placeholder="https://example.com/image.jpg"
                            @keyup.enter="insertImage"
                        />
                    </div>
                    <div v-if="imageTab === 'upload'" class="editor-modal-field">
                        <label>Choose Image</label>
                        <input
                            ref="imageInput"
                            type="file"
                            accept="image/*"
                            @change="handleImageUpload"
                        />
                        <div v-if="imagePreview" class="editor-modal-preview">
                            <img :src="imagePreview" alt="Preview" />
                        </div>
                    </div>
                    <div class="editor-modal-actions">
                        <button class="editor-modal-btn secondary" @click="closeImageModal">Cancel</button>
                        <button
                            class="editor-modal-btn primary"
                            @click="insertImage"
                            :disabled="imageTab === 'url' ? !imageUrl : !imagePreview"
                        >Insert</button>
                    </div>
                </div>
            </div>

            <!-- Video Modal -->
            <div v-if="showVideoModal" class="editor-modal-overlay" @click.self="closeVideoModal">
                <div class="editor-modal">
                    <button class="editor-modal-close" @click="closeVideoModal">×</button>
                    <h3 class="editor-modal-title">Insert Video</h3>
                    <div class="editor-modal-tabs">
                        <button
                            :class="{ active: videoTab === 'url' }"
                            @click="videoTab = 'url'"
                        >YouTube URL</button>
                        <button
                            :class="{ active: videoTab === 'upload' }"
                            @click="videoTab = 'upload'"
                        >Upload</button>
                    </div>
                    <div v-if="videoTab === 'url'" class="editor-modal-field">
                        <label>YouTube URL</label>
                        <input
                            v-model="videoUrl"
                            type="url"
                            placeholder="https://www.youtube.com/watch?v=..."
                            @keyup.enter="insertVideo"
                        />
                    </div>
                    <div v-if="videoTab === 'upload'" class="editor-modal-field">
                        <label>Choose Video</label>
                        <input
                            ref="videoInput"
                            type="file"
                            accept="video/*"
                            @change="handleVideoUpload"
                        />
                        <div v-if="videoPreview" class="editor-modal-preview">
                            <video :src="videoPreview" controls></video>
                        </div>
                    </div>
                    <div class="editor-modal-actions">
                        <button class="editor-modal-btn secondary" @click="closeVideoModal">Cancel</button>
                        <button
                            class="editor-modal-btn primary"
                            @click="insertVideo"
                            :disabled="videoTab === 'url' ? !videoUrl : !videoPreview"
                        >Insert</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'
import Highlight from '@tiptap/extension-highlight'
import { TextStyle } from '@tiptap/extension-text-style'
import { Color } from '@tiptap/extension-text-style'
import { Subscript } from '@tiptap/extension-subscript'
import { Superscript } from '@tiptap/extension-superscript'
import { Table, TableCell, TableHeader, TableRow } from '@tiptap/extension-table'
import { Node, mergeAttributes } from '@tiptap/core'

// Custom iframe extension for YouTube embeds
const Iframe = Node.create({
    name: 'iframe',
    group: 'block',
    atom: true,
    addAttributes() {
        return {
            src: { default: null },
            width: { default: 480 },
            height: { default: 270 },
            frameborder: { default: '0' },
            allowfullscreen: { default: 'true' }
        }
    },
    parseHTML() {
        return [{ tag: 'iframe' }]
    },
    renderHTML({ HTMLAttributes }) {
        return ['iframe', mergeAttributes(HTMLAttributes)]
    }
})

export default {
    name: 'TiptapEditor',
    components: {
        EditorContent
    },
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: ''
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            editor: null,
            // Dropdowns
            showColorPicker: false,
            showHighlightPicker: false,
            showTableMenu: false,
            // Color state
            currentColor: null,
            currentHighlight: null,
            // Text colors - organized by hue (rainbow order)
            colors: [
                '#d32f2f', '#ef6c00', '#f9a825', '#388e3c', '#79a0e8',  // vibrant: red, orange, yellow, green, blue
                '#e879a0', '#f0a5c0', '#a87cc9', '#c9a7d8', '#a5c0f0',  // soft: pink, light pink, purple, light purple, light blue
                '#4a3a5a', '#5a4a6a', '#6b5a7a', '#7b6a8a'              // dark text grays
            ],
            // Highlight colors - pastel tag colors (no dark purple)
            highlightColors: [
                '#ffb3ba', '#ffdfba', '#ffffba', '#baffc9', '#bae1ff',
                '#e0bbff', '#ffc8dd', '#bde0fe', '#a2d2ff'
            ],
            // Link modal
            showLinkModal: false,
            linkUrl: '',
            linkLabel: '',
            // Image modal
            showImageModal: false,
            imageTab: 'url',
            imageUrl: '',
            imagePreview: null,
            // Video modal
            showVideoModal: false,
            videoTab: 'url',
            videoUrl: '',
            videoPreview: null
        }
    },
    watch: {
        modelValue(value) {
            const isSame = this.editor?.getHTML() === value
            if (!isSame) {
                this.editor?.commands.setContent(value, false)
            }
        }
    },
    mounted() {
        this.editor = new Editor({
            extensions: [
                StarterKit,
                Placeholder.configure({
                    placeholder: this.placeholder
                }),
                Underline,
                Link.configure({
                    openOnClick: false,
                    HTMLAttributes: {
                        target: '_blank',
                        rel: 'noopener noreferrer'
                    }
                }),
                Image.configure({
                    inline: true,
                    allowBase64: true
                }),
                Highlight.configure({
                    multicolor: true
                }),
                TextStyle,
                Color,
                Subscript,
                Superscript,
                Table.configure({
                    resizable: true
                }),
                TableRow,
                TableCell,
                TableHeader,
                Iframe
            ],
            content: this.modelValue,
            onUpdate: () => {
                this.$emit('update:modelValue', this.editor.getHTML())
            }
        })

        document.addEventListener('click', this.closeAllDropdowns)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.closeAllDropdowns)
        this.editor?.destroy()
    },
    methods: {
        // Dropdown management
        closeAllDropdowns() {
            this.showColorPicker = false
            this.showHighlightPicker = false
            this.showTableMenu = false
        },
        toggleDropdown(type) {
            this.closeAllDropdowns()
            if (type === 'color') this.showColorPicker = true
            else if (type === 'highlight') this.showHighlightPicker = true
            else if (type === 'table') this.showTableMenu = true
        },

        // Subscript/Superscript (mutually exclusive)
        toggleSubscript() {
            if (this.editor?.isActive('superscript')) {
                this.editor?.chain().focus().unsetSuperscript().toggleSubscript().run()
            } else {
                this.editor?.chain().focus().toggleSubscript().run()
            }
        },
        toggleSuperscript() {
            if (this.editor?.isActive('subscript')) {
                this.editor?.chain().focus().unsetSubscript().toggleSuperscript().run()
            } else {
                this.editor?.chain().focus().toggleSuperscript().run()
            }
        },

        // Color
        setColor(color) {
            if (color) {
                this.editor?.chain().focus().setColor(color).run()
                this.currentColor = color
            } else {
                this.editor?.chain().focus().unsetColor().run()
                this.currentColor = null
            }
            this.showColorPicker = false
        },

        // Highlight
        setHighlight(color) {
            if (color) {
                this.editor?.chain().focus().setHighlight({ color }).run()
                this.currentHighlight = color
            } else {
                this.editor?.chain().focus().unsetHighlight().run()
                this.currentHighlight = null
            }
            this.showHighlightPicker = false
        },

        // Table operations
        insertTable() {
            this.editor?.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
            this.showTableMenu = false
        },
        addColumnBefore() {
            this.editor?.chain().focus().addColumnBefore().run()
            this.showTableMenu = false
        },
        addColumnAfter() {
            this.editor?.chain().focus().addColumnAfter().run()
            this.showTableMenu = false
        },
        deleteColumn() {
            this.editor?.chain().focus().deleteColumn().run()
            this.showTableMenu = false
        },
        addRowBefore() {
            this.editor?.chain().focus().addRowBefore().run()
            this.showTableMenu = false
        },
        addRowAfter() {
            this.editor?.chain().focus().addRowAfter().run()
            this.showTableMenu = false
        },
        deleteRow() {
            this.editor?.chain().focus().deleteRow().run()
            this.showTableMenu = false
        },
        deleteTable() {
            this.editor?.chain().focus().deleteTable().run()
            this.showTableMenu = false
        },

        // Link modal
        openLinkModal() {
            if (this.editor?.isActive('link')) {
                const attrs = this.editor.getAttributes('link')
                this.linkUrl = attrs.href || ''
            } else {
                this.linkUrl = ''
            }
            // Get selected text as label
            const { from, to } = this.editor.state.selection
            this.linkLabel = this.editor.state.doc.textBetween(from, to, '')
            this.showLinkModal = true
        },
        closeLinkModal() {
            this.showLinkModal = false
            this.linkUrl = ''
            this.linkLabel = ''
        },
        insertLink() {
            if (!this.linkUrl) return

            if (this.linkLabel && !this.editor.state.selection.empty) {
                // Replace selection with label
                this.editor?.chain()
                    .focus()
                    .deleteSelection()
                    .insertContent(`<a href="${this.linkUrl}" target="_blank" rel="noopener noreferrer">${this.linkLabel}</a>`)
                    .run()
            } else if (this.linkLabel) {
                // Insert new link with label
                this.editor?.chain()
                    .focus()
                    .insertContent(`<a href="${this.linkUrl}" target="_blank" rel="noopener noreferrer">${this.linkLabel}</a>`)
                    .run()
            } else if (!this.editor.state.selection.empty) {
                // Set link on selection
                this.editor?.chain()
                    .focus()
                    .extendMarkRange('link')
                    .setLink({ href: this.linkUrl })
                    .run()
            } else {
                // No label and no selection - use URL as text
                this.editor?.chain()
                    .focus()
                    .insertContent(`<a href="${this.linkUrl}" target="_blank" rel="noopener noreferrer">${this.linkUrl}</a>`)
                    .run()
            }
            this.closeLinkModal()
        },
        removeLink() {
            this.editor?.chain().focus().unsetLink().run()
            this.closeLinkModal()
        },

        // Image modal
        openImageModal() {
            this.imageTab = 'url'
            this.imageUrl = ''
            this.imagePreview = null
            this.showImageModal = true
        },
        closeImageModal() {
            this.showImageModal = false
            this.imageUrl = ''
            this.imagePreview = null
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = ''
            }
        },
        handleImageUpload(event) {
            const file = event.target.files[0]
            if (!file) return

            const reader = new FileReader()
            reader.onload = (e) => {
                this.imagePreview = e.target.result
            }
            reader.readAsDataURL(file)
        },
        insertImage() {
            const src = this.imageTab === 'url' ? this.imageUrl : this.imagePreview
            if (!src) return

            this.editor?.chain().focus().setImage({ src }).run()
            this.closeImageModal()
        },

        // Video modal
        openVideoModal() {
            this.videoTab = 'url'
            this.videoUrl = ''
            this.videoPreview = null
            this.showVideoModal = true
        },
        closeVideoModal() {
            this.showVideoModal = false
            this.videoUrl = ''
            this.videoPreview = null
            if (this.$refs.videoInput) {
                this.$refs.videoInput.value = ''
            }
        },
        handleVideoUpload(event) {
            const file = event.target.files[0]
            if (!file) return

            const reader = new FileReader()
            reader.onload = (e) => {
                this.videoPreview = e.target.result
            }
            reader.readAsDataURL(file)
        },
        getYoutubeVideoId(url) {
            // Extract video ID from various YouTube URL formats
            const patterns = [
                /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&?\s]+)/,
                /youtube\.com\/watch\?.*v=([^&?\s]+)/
            ]
            for (const pattern of patterns) {
                const match = url.match(pattern)
                if (match) return match[1]
            }
            return null
        },
        insertVideo() {
            if (this.videoTab === 'url') {
                if (!this.videoUrl) return
                const videoId = this.getYoutubeVideoId(this.videoUrl)
                if (videoId) {
                    // Insert YouTube iframe using privacy-enhanced mode (works better on localhost)
                    this.editor?.chain().focus().insertContent({
                        type: 'iframe',
                        attrs: {
                            src: `https://www.youtube-nocookie.com/embed/${videoId}`
                        }
                    }).run()
                }
            } else {
                if (!this.videoPreview) return
                // Insert as HTML5 video element
                this.editor?.chain().focus().insertContent(
                    `<video src="${this.videoPreview}" controls style="max-width: 100%;"></video>`
                ).run()
            }
            this.closeVideoModal()
        }
    }
}
</script>

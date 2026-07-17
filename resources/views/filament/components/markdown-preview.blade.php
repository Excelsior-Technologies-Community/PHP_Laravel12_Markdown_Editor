{{-- Live Markdown Multi-Format Preview Panel --}}
@php $postIdValue = $postId ?? 'new'; @endphp
<div
    x-data="{
        postId: '{{ $postIdValue }}',
        content: '',
        rendered: '',
        saved: false,
        autoSaveKey: 'post_autosave_{{ $postIdValue }}',
        previewFormat: 'standard',
        customComponents: {},
        templateVariables: {},

        init() {
            const saved = localStorage.getItem(this.autoSaveKey);
            if (saved) {
                const data = JSON.parse(saved);
                this.content = data.content ?? '';
                this.previewFormat = data.previewFormat ?? 'standard';
                this.customComponents = data.customComponents ?? {};
                this.templateVariables = data.templateVariables ?? {};
                this.renderMarkdown();
            }
            setInterval(() => this.syncContent(), 500);
            this.detectCustomComponents();
        },

        syncContent() {
            const cm = document.querySelector('.CodeMirror');
            if (cm && cm.CodeMirror) {
                const val = cm.CodeMirror.getValue();
                if (val !== this.content) {
                    this.content = val;
                    this.detectCustomComponents();
                    this.renderMarkdown();
                    this.autoSave();
                }
                return;
            }
            const textarea = document.querySelector('textarea[id*=content]');
            if (textarea && textarea.value !== this.content) {
                this.content = textarea.value;
                this.detectCustomComponents();
                this.renderMarkdown();
                this.autoSave();
            }
        },

        detectCustomComponents() {
            // Detect custom component patterns like {{component:name}} or [component:name]
            const componentPattern = /\{\{component:([a-zA-Z0-9_-]+)(?:\s+(.+?))?\}\}/g;
            const bracketPattern = /\[component:([a-zA-Z0-9_-]+)(?:\s+(.+?))?\]/g;
            
            this.customComponents = {};
            this.templateVariables = {};
            
            let match;
            while ((match = componentPattern.exec(this.content)) !== null) {
                const componentName = match[1];
                const params = match[2] || '';
                this.customComponents[componentName] = { type: 'mustache', params: this.parseParams(params) };
            }
            
            while ((match = bracketPattern.exec(this.content)) !== null) {
                const componentName = match[1];
                const params = match[2] || '';
                this.customComponents[componentName] = { type: 'bracket', params: this.parseParams(params) };
            }

            // Detect template variables like {{variable_name}}
            const varPattern = /\{\{([a-zA-Z0-9_]+)\}\}/g;
            while ((match = varPattern.exec(this.content)) !== null) {
                this.templateVariables[match[1]] = true;
            }
        },

        parseParams(paramsString) {
            if (!paramsString) return {};
            const params = {};
            const pairs = paramsString.split(/\s+/);
            pairs.forEach(pair => {
                const [key, value] = pair.split('=');
                if (key && value) {
                    params[key] = value.replace(/['"]/g, '');
                }
            });
            return params;
        },

        renderCustomComponent(componentName, component) {
            const params = component.params;
            
            // Custom component mapping logic
            switch(componentName) {
                case 'alert':
                    const alertType = params.type || 'info';
                    const alertColors = {
                        info: 'bg-blue-50 border-blue-200 text-blue-800',
                        warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
                        error: 'bg-red-50 border-red-200 text-red-800',
                        success: 'bg-green-50 border-green-200 text-green-800'
                    };
                    return `<div class="p-4 rounded-lg border ${alertColors[alertType] || alertColors.info} my-3">
                        <div class="font-semibold">${params.title || 'Notice'}</div>
                        <div class="text-sm mt-1">${params.message || 'Alert message'}</div>
                    </div>`;
                
                case 'button':
                    const btnStyle = params.style || 'primary';
                    const btnStyles = {
                        primary: 'bg-blue-600 hover:bg-blue-700 text-white',
                        secondary: 'bg-gray-600 hover:bg-gray-700 text-white',
                        outline: 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50'
                    };
                    return `<a href="${params.href || '#'}" class="inline-block px-4 py-2 rounded-lg ${btnStyles[btnStyle] || btnStyles.primary} text-sm font-medium my-2">
                        ${params.label || 'Button'}
                    </a>`;
                
                case 'card':
                    return `<div class="border rounded-lg p-4 shadow-sm bg-gray-50 dark:bg-gray-800 my-3">
                        ${params.title ? `<h3 class="font-bold text-lg mb-2">${params.title}</h3>` : ''}
                        <div class="text-sm">${params.content || 'Card content'}</div>
                    </div>`;
                
                case 'badge':
                    const badgeColor = params.color || 'blue';
                    const badgeColors = {
                        blue: 'bg-blue-100 text-blue-800',
                        green: 'bg-green-100 text-green-800',
                        red: 'bg-red-100 text-red-800',
                        yellow: 'bg-yellow-100 text-yellow-800'
                    };
                    return `<span class="px-2 py-1 rounded-full text-xs font-medium ${badgeColors[badgeColor] || badgeColors.blue}">
                        ${params.text || 'Badge'}
                    </span>`;
                
                case 'code-block':
                    const language = params.lang || 'text';
                    return `<div class="bg-gray-900 text-gray-100 p-4 rounded-lg my-3 overflow-x-auto">
                        <div class="text-xs text-gray-400 mb-2">${language}</div>
                        <pre class="text-sm font-mono"><code>${this.escapeHtml(params.code || 'Code here')}</code></pre>
                    </div>`;
                
                case 'divider':
                    return `<hr class="my-4 border-gray-300 dark:border-gray-600 ${params.dashed ? 'border-dashed' : ''}">`;
                
                case 'image':
                    return `<div class="my-3">
                        <img src="${params.src || ''}" alt="${params.alt || 'Image'}" class="rounded-lg max-w-full ${params.rounded ? 'rounded-full' : ''}" style="max-height: ${params.maxHeight || '400px'}">
                        ${params.caption ? `<p class="text-xs text-gray-500 mt-1 text-center">${params.caption}</p>` : ''}
                    </div>`;
                
                case 'video':
                    return `<div class="my-3">
                        <div class="aspect-video bg-gray-900 rounded-lg flex items-center justify-center">
                            <span class="text-white text-sm">Video: ${params.src || 'No source'}</span>
                        </div>
                        ${params.caption ? `<p class="text-xs text-gray-500 mt-1 text-center">${params.caption}</p>` : ''}
                    </div>`;
                
                case 'table':
                    return `<div class="overflow-x-auto my-3">
                        <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700">
                                    ${params.headers ? params.headers.split(',').map(h => `<th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left text-sm">${h.trim()}</th>`).join('') : '<th class="border px-3 py-2">Header</th>'}
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    ${params.headers ? params.headers.split(',').map(() => `<td class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm">Cell</td>`).join('') : '<td class="border px-3 py-2">Cell</td>'}
                                </tr>
                            </tbody>
                        </table>
                    </div>`;
                
                default:
                    return `<div class="p-3 bg-yellow-50 border border-yellow-200 rounded text-yellow-800 text-sm my-2">
                        Unknown component: <strong>${componentName}</strong>
                    </div>`;
            }
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        renderMarkdown() {
            let t = this.content
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            // Replace custom components first
            t = t.replace(/\{\{component:([a-zA-Z0-9_-]+)(?:\s+(.+?))?\}\}/g, (match, name, params) => {
                if (this.customComponents[name]) {
                    return this.renderCustomComponent(name, this.customComponents[name]);
                }
                return match;
            });

            t = t.replace(/\[component:([a-zA-Z0-9_-]+)(?:\s+(.+?))?\]/g, (match, name, params) => {
                if (this.customComponents[name]) {
                    return this.renderCustomComponent(name, this.customComponents[name]);
                }
                return match;
            });

            // Standard markdown rendering based on preview format
            if (this.previewFormat === 'minimal') {
                t = t
                    .replace(/^#{3}\s(.+)$/gm, '<h3 class="text-lg font-semibold mt-2 mb-1">$1</h3>')
                    .replace(/^#{2}\s(.+)$/gm, '<h2 class="text-xl font-semibold mt-3 mb-2">$1</h2>')
                    .replace(/^#{1}\s(.+)$/gm, '<h1 class="text-2xl font-bold mt-3 mb-2">$1</h1>')
                    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.+?)\*/g, '<em>$1</em>')
                    .replace(/`([^`]+)`/g, '<code class="bg-gray-100 dark:bg-gray-800 px-1 rounded text-xs">$1</code>')
                    .replace(/\n\n/g, '</p><p class="mb-2">')
                    .replace(/\n/g, '<br>');
            } else if (this.previewFormat === 'rich') {
                t = t
                    .replace(/^#{3}\s(.+)$/gm, '<h3 class="text-2xl font-bold mt-4 mb-2 text-gray-800 dark:text-gray-100">$1</h3>')
                    .replace(/^#{2}\s(.+)$/gm, '<h2 class="text-3xl font-bold mt-6 mb-3 text-gray-900 dark:text-white border-b-2 border-gray-200 dark:border-gray-700 pb-2">$1</h2>')
                    .replace(/^#{1}\s(.+)$/gm, '<h1 class="text-4xl font-extrabold mt-8 mb-4 text-gray-900 dark:text-white">$1</h1>')
                    .replace(/\*\*(.+?)\*\*/g, '<strong class="font-bold text-gray-900 dark:text-white">$1</strong>')
                    .replace(/\*(.+?)\*/g, '<em class="italic text-gray-700 dark:text-gray-300">$1</em>')
                    .replace(/`([^`]+)`/g, '<code class="bg-gradient-to-r from-purple-100 to-pink-100 dark:from-purple-900 dark:to-pink-900 px-2 py-1 rounded-lg text-sm font-mono text-purple-800 dark:text-purple-200">$1</code>')
                    .replace(/^&gt;\s(.+)$/gm, '<blockquote class="border-l-4 border-purple-500 pl-4 italic text-gray-600 dark:text-gray-400 my-4 bg-purple-50 dark:bg-purple-900/20 py-2 rounded-r">$1</blockquote>')
                    .replace(/^\s*[-*]\s(.+)$/gm, '<li class="ml-6 list-disc text-gray-700 dark:text-gray-300 mb-1">$1</li>')
                    .replace(/^\s*\d+\.\s(.+)$/gm, '<li class="ml-6 list-decimal text-gray-700 dark:text-gray-300 mb-1">$1</li>')
                    .replace(/^---$/gm, '<hr class="my-6 border-t-2 border-gray-300 dark:border-gray-600">')
                    .replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 underline font-medium" target="_blank">$1</a>')
                    .replace(/\n\n/g, '</p><p class="mb-4 text-gray-700 dark:text-gray-300 leading-relaxed">')
                    .replace(/\n/g, '<br>');
            } else {
                // Standard format (default)
                t = t
                    .replace(/^#{3}\s(.+)$/gm, '<h3 class="text-xl font-bold mt-3 mb-1">$1</h3>')
                    .replace(/^#{2}\s(.+)$/gm, '<h2 class="text-2xl font-bold mt-4 mb-2 border-b pb-1">$1</h2>')
                    .replace(/^#{1}\s(.+)$/gm, '<h1 class="text-3xl font-bold mt-4 mb-2">$1</h1>')
                    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.+?)\*/g, '<em>$1</em>')
                    .replace(/`([^`]+)`/g, '<code class="bg-gray-100 dark:bg-gray-700 px-1 rounded text-sm font-mono">$1</code>')
                    .replace(/^&gt;\s(.+)$/gm, '<blockquote class="border-l-4 border-blue-400 pl-4 italic text-gray-500 my-2">$1</blockquote>')
                    .replace(/^\s*[-*]\s(.+)$/gm, '<li class="ml-5 list-disc">$1</li>')
                    .replace(/^\s*\d+\.\s(.+)$/gm, '<li class="ml-5 list-decimal">$1</li>')
                    .replace(/^---$/gm, '<hr class="my-4 border-gray-300">')
                    .replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2" class="text-blue-500 underline" target="_blank">$1</a>')
                    .replace(/\n\n/g, '</p><p class="mb-3">')
                    .replace(/\n/g, '<br>');
            }

            this.rendered = '<p class="mb-3">' + t + '</p>';
        },

        setFormat(format) {
            this.previewFormat = format;
            this.renderMarkdown();
            this.autoSave();
        },

        autoSave() {
            localStorage.setItem(this.autoSaveKey, JSON.stringify({
                content: this.content,
                previewFormat: this.previewFormat,
                customComponents: this.customComponents,
                templateVariables: this.templateVariables,
                savedAt: new Date().toISOString()
            }));
            this.saved = true;
            setTimeout(() => this.saved = false, 2000);
        },

        clearSave() {
            localStorage.removeItem(this.autoSaveKey);
            this.rendered = '';
            this.content = '';
            this.customComponents = {};
            this.templateVariables = {};
        }
    }"
    class="w-full"
>
    <div class="flex items-center justify-between mb-2 px-1">
        <span class="text-sm font-semibold text-gray-600 dark:text-gray-300 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Live Multi-Format Preview
        </span>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-1">
                <button @click="setFormat('minimal')" :class="previewFormat === 'minimal' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700'" class="text-xs px-2 py-1 rounded">Minimal</button>
                <button @click="setFormat('standard')" :class="previewFormat === 'standard' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700'" class="text-xs px-2 py-1 rounded">Standard</button>
                <button @click="setFormat('rich')" :class="previewFormat === 'rich' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700'" class="text-xs px-2 py-1 rounded">Rich</button>
            </div>
            <span x-show="saved" x-transition class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Auto-saved
            </span>
            <button type="button" @click="clearSave()" class="text-xs text-red-400 hover:text-red-600 underline">
                Clear Draft
            </button>
        </div>
    </div>

    <!-- Custom Components Detected -->
    <div x-show="Object.keys(customComponents).length > 0" class="mb-2 px-1">
        <span class="text-xs text-purple-600 dark:text-purple-400 font-medium">Components detected:</span>
        <template x-for="(comp, name) in customComponents" :key="name">
            <span class="inline-block text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded ml-1" x-text="name"></span>
        </template>
    </div>

    <div class="min-h-[300px] max-h-[600px] overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 p-5 shadow-inner">
        <template x-if="rendered !== ''">
            <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 text-sm leading-relaxed" x-html="rendered"></div>
        </template>
        <template x-if="rendered === ''">
            <div class="flex flex-col items-center justify-center h-48 text-gray-400 dark:text-gray-600">
                <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm">Start typing to see live preview...</p>
                <p class="text-xs mt-1 opacity-60">Use {{component:name}} for custom components</p>
                <p class="text-xs mt-1 opacity-60">Content auto-saves to browser storage</p>
            </div>
        </template>
    </div>
</div>

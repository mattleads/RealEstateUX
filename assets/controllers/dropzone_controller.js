import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['input', 'preview', 'placeholder'];

    connect() {
        this.element.addEventListener('dragover', this.onDragOver.bind(this));
        this.element.addEventListener('dragleave', this.onDragLeave.bind(this));
        this.element.addEventListener('drop', this.onDrop.bind(this));
        this.element.addEventListener('click', this.onClick.bind(this));
    }

    onDragOver(e) {
        e.preventDefault();
        this.element.classList.add('border-blue-500', 'bg-blue-50');
    }

    onDragLeave(e) {
        e.preventDefault();
        this.element.classList.remove('border-blue-500', 'bg-blue-50');
    }

    onDrop(e) {
        e.preventDefault();
        this.element.classList.remove('border-blue-500', 'bg-blue-50');

        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            this.inputTarget.files = e.dataTransfer.files;
            this.updatePreview(e.dataTransfer.files[0]);
        }
    }

    onClick() {
        this.inputTarget.click();
    }

    handleFiles(e) {
        if (e.target.files && e.target.files.length > 0) {
            this.updatePreview(e.target.files[0]);
        }
    }

    updatePreview(file) {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewTarget.src = e.target.result;
                this.previewTarget.classList.remove('hidden');
                if (this.hasPlaceholderTarget) {
                    this.placeholderTarget.classList.add('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    }
}

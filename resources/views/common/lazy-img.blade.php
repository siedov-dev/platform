<script
    src="//cdnjs.cloudflare.com/ajax/libs/document-register-element/1.13.0/document-register-element.js"
>/* W3C Custom Elements */</script>
<script>
    class LazyImg extends HTMLImageElement {
        constructor() {
            super();
            this.initialized = false;

            this.scrollHandler = () => {
                if (!this.initialized) {
                    this.updateVisibility();
                }
            };
            document.addEventListener('scroll', this.scrollHandler);
        }

        updateVisibility() {
            if (!this.initialized && this.isVisible()) {
                if (this.dataset.fallbackImg) {
                    this.onerror = () => {
                        this.src = this.dataset.fallbackImg;
                    }
                }
                this.src = this.dataset.src;
                this.initialized = true;
                document.removeEventListener('scroll', this.scrollHandler);
            }
        }

        isVisible() {
            let h = window.innerHeight;
            let rect = this.getBoundingClientRect();
            return rect.top < h + 300;
        }

        connectedCallback() {
            console.log('connected');
            this.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzIDIiPjwvc3ZnPg==';
            this.style.aspectRatio = this.dataset.aspectRatio || 'auto';
            this.updateVisibility();
        }

        disconnectedCallback() {
            document.removeEventListener('scroll', this.scrollHandler);
        }

        attributeChangedCallback(name, oldValue, newValue) {
            if (name === 'data-src') {
                if (this.src !== '' || this.isVisible()) {
                    this.src = newValue;
                }
            }
        }
    }
    customElements.define('lazy-img', LazyImg, {extends: 'img'});
</script>

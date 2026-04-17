import { Controller } from '@hotwired/stimulus';
import Typed from 'typed.js';

export default class extends Controller {
    static values = {
        strings: Array,
        speed: { type: Number, default: 50 }
    }

    connect() {
        this.typed = new Typed(this.element, {
            strings: this.stringsValue,
            typeSpeed: this.speedValue,
            loop: true,
        });
    }

    disconnect() {
        if (this.typed) {
            this.typed.destroy();
        }
    }
}

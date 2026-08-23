import Alpine from 'alpinejs';
import './knowledge-parser.js';

import { createKnowledgeManager } from './knowledge-admin.js';

window.Alpine = Alpine;

// Store global UI states if needed
document.addEventListener('alpine:init', () => {
    Alpine.data('knowledgeManager', createKnowledgeManager);

    Alpine.data('heroSlider', () => ({
        currentSlide: 0,
        totalSlides: 3,
        autoplayInterval: null,
        
        init() {
            this.startAutoplay();
        },
        
        next() {
            this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
        },
        
        prev() {
            this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        },
        
        goTo(index) {
            this.currentSlide = index;
            this.resetAutoplay();
        },
        
        startAutoplay() {
            this.autoplayInterval = setInterval(() => {
                this.next();
            }, 5500);
        },
        
        pauseAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
            }
        },
        
        resetAutoplay() {
            this.pauseAutoplay();
            this.startAutoplay();
        }
    }));

    Alpine.data('productFilter', () => ({
        activeCategory: 'all',
        
        setCategory(cat) {
            this.activeCategory = cat;
        },
        
        isVisible(category, type) {
            if (this.activeCategory === 'all') return true;
            if (this.activeCategory === 'curah') return type.includes('curah');
            if (this.activeCategory === 'ready-to-cook') return type.includes('ready');
            return category === this.activeCategory;
        }
    }));
});

Alpine.start();

document.addEventListener('DOMContentLoaded', () => setTimeout(() => document.querySelectorAll('.wp-block-roxane-critic .critic').forEach(el => el.style.setProperty('--critic-header-after-width', (((el.querySelector('.critic-header').clientWidth - (el.querySelector('.movie-rating')?.offsetLeft + 100) + 16)) ?? 16) + 'px')), 150));
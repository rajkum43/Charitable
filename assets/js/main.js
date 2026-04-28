// Initialize AOS Animation
AOS.init({
    duration: 1000,
    once: true,
    offset: 100,
    easing: 'ease-in-out'
});

// Initialize Bootstrap Carousel for Hero Slider
document.addEventListener('DOMContentLoaded', function() {
    const heroCarousel = document.getElementById('heroCarousel');
    if (heroCarousel) {
        const carousel = new bootstrap.Carousel(heroCarousel, {
            interval: 5000,
            pause: 'hover',
            ride: true
        });
    }
});

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Hero Slider
    const heroSwiper = new Swiper('.heroSwiper', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        speed: 1000,
    });

    // Initialize Testimonial Slider
    const testimonialSlides = document.querySelectorAll('.testimonialSwiper .swiper-slide').length;
    const testimonialSwiper = new Swiper('.testimonialSwiper', {
        loop: testimonialSlides > 3,
        loopPreventsSlide: false,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            320: {
                slidesPerView: 1,
                spaceBetween: 20
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 30
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 40
            }
        }
    });

    // Initialize Partner Slider
    const partnerSlides = document.querySelectorAll('.partnerSwiper .swiper-slide').length;
    const partnerSwiper = new Swiper('.partnerSwiper', {
        loop: partnerSlides > 5,
        loopPreventsSlide: false,
        autoplay: {
            delay: 2000,
            disableOnInteraction: false,
        },
        breakpoints: {
            320: {
                slidesPerView: 2,
                spaceBetween: 20
            },
            576: {
                slidesPerView: 3,
                spaceBetween: 30
            },
            768: {
                slidesPerView: 4,
                spaceBetween: 30
            },
            1024: {
                slidesPerView: 5,
                spaceBetween: 30
            }
        }
    });

    // Counter Animation with Progress
    const counters = document.querySelectorAll('.counter');
    const speed = 200;

    const startCounter = (counter) => {
        const updateCount = () => {
            const target = parseInt(counter.getAttribute('data-target'));
            const count = parseInt(counter.innerText);
            const increment = Math.trunc(target / speed);

            if (count < target) {
                counter.innerText = count + increment;
                setTimeout(updateCount, 1);
            } else {
                counter.innerText = target + '+';
                
                // Add sparkle effect
                counter.parentElement.classList.add('sparkle');
                setTimeout(() => {
                    counter.parentElement.classList.remove('sparkle');
                }, 1000);
            }
        };

        updateCount();
    };

    // Intersection Observer for counters
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                startCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    counters.forEach(counter => {
        observer.observe(counter);
    });
});

// Navbar Scroll Effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('shadow-lg');
        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
    } else {
        navbar.classList.remove('shadow-lg');
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
    }
});

// Smooth Scroll for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Mobile Sidebar Menu Handler
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('navbarMain');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const body = document.body;
    
    if (menuToggle && navbarToggler) {
        // Handle sidebar collapse
        menuToggle.addEventListener('show.bs.collapse', function() {
            body.classList.add('sidebar-open');
            body.style.overflow = 'hidden';
        });

        menuToggle.addEventListener('hide.bs.collapse', function() {
            body.classList.remove('sidebar-open');
            body.style.overflow = 'auto';
            menuToggle.classList.remove('dropdown-active');
        });
    }

    // Handle dropdown show/hide to control overlay
    const dropdownToggles = document.querySelectorAll('.navbar-nav .dropdown-toggle');
    dropdownToggles.forEach((toggle) => {
        // When dropdown shows
        toggle.addEventListener('show.bs.dropdown', function() {
            if (window.innerWidth < 992 && menuToggle) {
                menuToggle.classList.add('dropdown-active');
            }
        });

        // When dropdown hides
        toggle.addEventListener('hide.bs.dropdown', function() {
            if (window.innerWidth < 992 && menuToggle) {
                menuToggle.classList.remove('dropdown-active');
            }
        });
    });

    // Close sidebar when clicking on regular nav links (not dropdowns)
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
    navLinks.forEach((link) => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992 && menuToggle) {
                const bsCollapse = new bootstrap.Collapse(menuToggle, {toggle: false});
                bsCollapse.hide();
            }
        });
    });
});

// Form Validation with Animation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            input.classList.add('shake');
            isValid = false;
            
            setTimeout(() => {
                input.classList.remove('shake');
            }, 500);
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Back to Top Button with Animation
const backToTopBtn = document.createElement('button');
backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
backToTopBtn.className = 'btn btn-primary back-to-top';
backToTopBtn.style.cssText = `
    position: fixed;
    bottom: 30px;
    right: 30px;
    display: none;
    z-index: 99;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    transition: all 0.3s ease;
`;

document.body.appendChild(backToTopBtn);

window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
        backToTopBtn.style.display = 'block';
        backToTopBtn.style.animation = 'fadeInUp 0.5s ease';
    } else {
        backToTopBtn.style.display = 'none';
    }
});

backToTopBtn.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Add hover effect to buttons
document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-3px)';
    });
    
    btn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// Lazy Loading Images
const images = document.querySelectorAll('img[data-src]');
const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.add('fade-in');
            observer.unobserve(img);
        }
    });
});

images.forEach(img => imageObserver.observe(img));

// Page Load Animation
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
    
    // Remove loader if exists
    const loader = document.querySelector('.loader-wrapper');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }, 500);
    }
});
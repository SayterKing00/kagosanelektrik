document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Navbar
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // 2. Mobile Menu Toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const closeMenuBtn = document.querySelector('.close-menu-btn');
    const mobileOverlay = document.querySelector('.mobile-menu-overlay');
    const mobileLinks = document.querySelectorAll('.mobile-nav-links a');

    const toggleMenu = () => {
        mobileOverlay.classList.toggle('active');
        document.body.style.overflow = mobileOverlay.classList.contains('active') ? 'hidden' : '';
    };

    mobileBtn.addEventListener('click', toggleMenu);
    closeMenuBtn.addEventListener('click', toggleMenu);
    
    mobileLinks.forEach(link => {
        link.addEventListener('click', toggleMenu);
    });

    // 3. Contact Modal Logic
    const modal = document.getElementById('contactModal');
    const openModalBtns = document.querySelectorAll('.open-contact-modal');
    const closeModalBtn = document.getElementById('closeModalBtn');

    const openModal = (e) => {
        if(e) e.preventDefault();
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // If mobile menu is open, close it
        if(mobileOverlay.classList.contains('active')){
            mobileOverlay.classList.remove('active');
        }
    };

    const closeModal = () => {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    };

    openModalBtns.forEach(btn => {
        btn.addEventListener('click', openModal);
    });

    closeModalBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // 4. Mouse move effect for Service Cards (Uiverse style glow)
    document.getElementById('services').onmousemove = e => {
        for(const card of document.querySelectorAll('.service-card-uiverse')) {
            const rect = card.getBoundingClientRect(),
                  x = e.clientX - rect.left,
                  y = e.clientY - rect.top;

            card.style.setProperty("--mouse-x", `${x}px`);
            card.style.setProperty("--mouse-y", `${y}px`);
        };
    }

    // 5. Counter Animation for 'Why Us' section
    const counters = document.querySelectorAll('.counter');
    const speed = 200; // The lower the slower

    const animateCounters = () => {
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const countText = counter.innerText.replace(/[^0-9]/g, '');
                const count = +countText;
                const inc = target / speed;
                const suffix = counter.getAttribute('data-suffix') || '';

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc) + suffix;
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target + suffix;
                }
            };
            updateCount();
        });
    };

    // Use Intersection Observer to trigger counter animation when in view
    const observerOptions = {
        threshold: 0.5
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const whyUsSection = document.querySelector('.why-us');
    if (whyUsSection) {
        observer.observe(whyUsSection);
    }

    // 6. Smooth Scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
  
                window.scrollTo({
                     top: offsetPosition,
                     behavior: "smooth"
                });
            }
        });
    });

    // 8. FAQ Accordion
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => {
            // Close other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) otherItem.classList.remove('active');
            });
            item.classList.toggle('active');
        });
    });

    // 9. Form Submission via Web3Forms API
    const form = document.getElementById('contactForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const name = document.getElementById('name').value;
            const phone = document.getElementById('phone').value;
            const message = document.getElementById('message').value;

            const btn = form.querySelector('.submit-btn-uiverse .text');
            const originalText = btn.innerText;
            btn.innerText = 'Gönderiliyor...';
            
            // Web3Forms API isteği
            fetch('https://api.web3forms.com/submit', {
                method: "POST",
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    access_key: "0ac7530e-6273-4809-94c4-66a264722930",
                    subject: "Web Sitesinden Yeni Mesaj: " + name,
                    from_name: "Mamak Elektrik Web Sitesi",
                    name: name,
                    phone: phone,
                    message: message
                })
            })
            .then(async (response) => {
                let json = await response.json();
                if(response.status == 200) {
                    btn.innerText = 'Gönderildi!';
                    form.reset();
                } else {
                    console.log(json);
                    btn.innerText = 'Hata Oluştu!';
                }
                setTimeout(() => {
                    btn.innerText = originalText;
                }, 3000);
            })
            .catch(error => {
                console.error("Fetch Hatası:", error);
                btn.innerText = 'Hata Oluştu!';
                setTimeout(() => {
                    btn.innerText = originalText;
                }, 3000);
            });
        });
    }

    // 10. Reviews Marquee Logic
    const marqueeTrack = document.getElementById('reviewsTrack');
    const toggleBtn = document.getElementById('marqueeToggleBtn');
    
    if(marqueeTrack && toggleBtn) {
        // Clone items for infinite scroll effect
        const reviewCards = marqueeTrack.querySelectorAll('.review-card');
        reviewCards.forEach(card => {
            const clone = card.cloneNode(true);
            marqueeTrack.appendChild(clone);
        });

        // Toggle pause/play
        let isPaused = false;
        toggleBtn.addEventListener('click', () => {
            isPaused = !isPaused;
            if(isPaused) {
                marqueeTrack.classList.add('paused');
                toggleBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            } else {
                marqueeTrack.classList.remove('paused');
                toggleBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
            }
        });
    }
});

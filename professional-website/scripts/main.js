/**
 * Site Web Professionnel - Script Principal
 * Comptable & Développeur Web
 */

// ===== VARIABLES GLOBALES =====
let currentTheme = localStorage.getItem('theme') || 'light';
let isMenuOpen = false;
let currentSlide = 0;
let chatbotOpen = false;

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    initializeTheme();
    initializeLoader();
    initializeNavigation();
    initializeScrollEffects();
    initializeAnimations();
    initializeStats();
    initializeFAQ();
    initializeCarousel();
    initializeChatbot();
    initializeContactForm();
    initializeBackToTop();
    initializeSmoothScroll();
});

// ===== GESTION DU THÈME =====
function initializeTheme() {
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon();
}

function toggleTheme() {
    currentTheme = currentTheme === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    localStorage.setItem('theme', currentTheme);
    updateThemeIcon();
    
    // Animation de transition
    document.body.style.transition = 'all 0.3s ease';
    setTimeout(() => {
        document.body.style.transition = '';
    }, 300);
}

function updateThemeIcon() {
    const themeIcon = document.querySelector('.theme-toggle i');
    if (themeIcon) {
        themeIcon.className = currentTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }
}

// ===== LOADER =====
function initializeLoader() {
    const loader = document.querySelector('.loader');
    if (loader) {
        // Animation de disparition du loader
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }, 2000);
    }
}

// ===== NAVIGATION =====
function initializeNavigation() {
    const navbar = document.querySelector('.navbar');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Scroll navbar
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        updateScrollProgress();
    });
    
    // Menu mobile
    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleMobileMenu);
    }
    
    // Fermer le menu mobile au clic sur un lien
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (isMenuOpen) {
                toggleMobileMenu();
            }
        });
    });
    
    // Marquer le lien actif
    updateActiveLink();
    window.addEventListener('scroll', updateActiveLink);
}

function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    
    isMenuOpen = !isMenuOpen;
    navMenu.classList.toggle('active');
    
    // Animation de l'icône hamburger
    if (mobileToggle) {
        mobileToggle.innerHTML = isMenuOpen ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
    }
}

function updateActiveLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 200;
        if (window.scrollY >= sectionTop) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
            link.classList.add('active');
        }
    });
}

// ===== BARRE DE PROGRESSION DU SCROLL =====
function updateScrollProgress() {
    const scrollProgress = document.querySelector('.scroll-progress');
    if (scrollProgress) {
        const windowHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (window.scrollY / windowHeight) * 100;
        scrollProgress.style.width = scrolled + '%';
    }
}

// ===== ANIMATIONS AU SCROLL OPTIMISÉES =====
function initializeScrollEffects() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                
                // Animations différentes selon la position et le type d'élément
                const rect = element.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                const elementCenter = rect.top + rect.height / 2;
                const windowCenter = windowHeight / 2;
                
                // Définir l'animation selon la position
                let animationClass = 'slideInUp';
                if (elementCenter < windowCenter) {
                    animationClass = element.classList.contains('card') ? 'slideInLeft' : 'fadeInUp';
                } else {
                    animationClass = element.classList.contains('card') ? 'slideInRight' : 'slideInUp';
                }
                
                // Ajouter un délai pour les éléments en groupe
                const siblings = element.parentElement?.querySelectorAll('.animate-on-scroll') || [];
                const index = Array.from(siblings).indexOf(element);
                const delay = Math.min(index * 100, 500); // Max 500ms de délai
                
                setTimeout(() => {
                    element.style.animation = `${animationClass} 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards`;
                    element.classList.add('visible');
                }, delay);
                
                observer.unobserve(element);
            }
        });
    }, observerOptions);
    
    // Préparer et observer les éléments
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
    
    // Effet parallaxe optimisé avec throttling
    let ticking = false;
    function updateParallax() {
        const parallaxElements = document.querySelectorAll('.parallax');
        const scrolled = window.pageYOffset;
        
        parallaxElements.forEach((element, index) => {
            const rate = scrolled * (-0.3 - index * 0.1); // Vitesses différentes
            element.style.transform = `translate3d(0, ${rate}px, 0)`;
        });
        
        ticking = false;
    }
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }, { passive: true });
}

// ===== ANIMATIONS GÉNÉRALES =====
function initializeAnimations() {
    // Animation des éléments au survol avec transition CSS pour de meilleures performances
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        // Ajouter les styles de transition
        card.style.transition = 'transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.3s ease';
        card.style.willChange = 'transform';
        
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translate3d(0, -10px, 0) scale(1.02)';
            card.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translate3d(0, 0, 0) scale(1)';
            card.style.boxShadow = '';
        });
    });
    
    // Animation des boutons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Effet ripple
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255,255,255,0.6);
                transform: scale(0);
                animation: ripple 0.6s linear;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
            `;
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

// ===== STATISTIQUES ANIMÉES =====
function initializeStats() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    const animateStats = () => {
        statNumbers.forEach(stat => {
            const target = parseInt(stat.getAttribute('data-target') || stat.textContent);
            const increment = target / 100;
            let current = 0;
            
            const updateStat = () => {
                if (current < target) {
                    current += increment;
                    stat.textContent = Math.ceil(current);
                    requestAnimationFrame(updateStat);
                } else {
                    stat.textContent = target;
                }
            };
            
            updateStat();
        });
    };
    
    // Observer pour déclencher l'animation
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStats();
                statsObserver.unobserve(entry.target);
            }
        });
    });
    
    const statsContainer = document.querySelector('.stats-container');
    if (statsContainer) {
        statsObserver.observe(statsContainer);
    }
}

// ===== FAQ ACCORDION =====
function initializeFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Fermer tous les autres items
            faqItems.forEach(otherItem => {
                otherItem.classList.remove('active');
            });
            
            // Toggle l'item actuel
            if (!isActive) {
                item.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + 'px';
            } else {
                answer.style.maxHeight = '0';
            }
        });
    });
}

// ===== CARROUSEL =====
function initializeCarousel() {
    const carousel = document.querySelector('.carousel');
    if (!carousel) return;
    
    const container = carousel.querySelector('.carousel-container');
    const slides = carousel.querySelectorAll('.carousel-slide');
    const prevBtn = carousel.querySelector('.carousel-prev');
    const nextBtn = carousel.querySelector('.carousel-next');
    const indicators = carousel.querySelectorAll('.carousel-indicator');
    
    function updateCarousel() {
        if (container) {
            container.style.transform = `translateX(-${currentSlide * 100}%)`;
        }
        
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentSlide);
        });
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        updateCarousel();
    }
    
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        updateCarousel();
    }
    
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            currentSlide = index;
            updateCarousel();
        });
    });
    
    // Auto-play
    setInterval(nextSlide, 5000);
}

// ===== CHATBOT =====
function initializeChatbot() {
    const chatbotToggle = document.querySelector('.chatbot-toggle');
    const chatbotContainer = document.querySelector('.chatbot-container');
    const chatbotInput = document.querySelector('.chatbot-input');
    const chatbotSend = document.querySelector('.chatbot-send');
    const chatbotMessages = document.querySelector('.chatbot-messages');
    
    // Réponses prédéfinies du chatbot
    const botResponses = {
        'bonjour': 'Bonjour ! Je suis votre assistant virtuel. Comment puis-je vous aider aujourd\'hui ?',
        'services': 'Je propose deux types de services :\n\n📊 **Comptabilité** :\n- Tenue de comptabilité\n- Déclarations fiscales\n- Conseils financiers\n\n💻 **Développement Web** :\n- Sites web sur mesure\n- Applications web\n- Maintenance et support',
        'tarifs': 'Les tarifs varient selon le projet. Contactez-moi pour un devis personnalisé gratuit ! Utilisez le formulaire de contact ou appelez-moi directement.',
        'contact': 'Vous pouvez me contacter via :\n- Le formulaire de contact sur le site\n- Email : contact@monsite.com\n- Téléphone : 01 23 45 67 89\n- LinkedIn : Mon profil professionnel',
        'portfolio': 'Consultez ma page Portfolio pour découvrir mes derniers projets en comptabilité et développement web. Chaque projet reflète mon expertise et ma passion pour l\'excellence.',
        'experience': 'J\'ai plus de 5 ans d\'expérience en comptabilité et 3 ans en développement web. Cette double compétence me permet d\'offrir des solutions complètes à mes clients.',
        'merci': 'Je vous en prie ! N\'hésitez pas si vous avez d\'autres questions. Je suis là pour vous aider ! 😊'
    };
    
    if (chatbotToggle) {
        chatbotToggle.addEventListener('click', toggleChatbot);
    }
    
    if (chatbotSend) {
        chatbotSend.addEventListener('click', sendMessage);
    }
    
    if (chatbotInput) {
        chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
    
    function toggleChatbot() {
        chatbotOpen = !chatbotOpen;
        if (chatbotContainer) {
            chatbotContainer.style.display = chatbotOpen ? 'flex' : 'none';
        }
        
        if (chatbotOpen && chatbotMessages && chatbotMessages.children.length === 0) {
            addBotMessage('Bonjour ! Je suis votre assistant virtuel. Comment puis-je vous aider aujourd\'hui ?');
        }
    }
    
    function sendMessage() {
        const input = chatbotInput;
        if (!input || !input.value.trim()) return;
        
        const message = input.value.trim();
        addUserMessage(message);
        input.value = '';
        
        // Simuler un délai de traitement
        setTimeout(() => {
            const response = getBotResponse(message);
            addBotMessage(response);
        }, 1000);
    }
    
    function addUserMessage(message) {
        const messageEl = document.createElement('div');
        messageEl.className = 'chatbot-message user';
        messageEl.textContent = message;
        
        if (chatbotMessages) {
            chatbotMessages.appendChild(messageEl);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }
    }
    
    function addBotMessage(message) {
        // Afficher l'indicateur de frappe
        showTypingIndicator();
        
        setTimeout(() => {
            hideTypingIndicator();
            
            const messageEl = document.createElement('div');
            messageEl.className = 'chatbot-message bot';
            messageEl.innerHTML = message.replace(/\n/g, '<br>');
            
            if (chatbotMessages) {
                chatbotMessages.appendChild(messageEl);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
        }, 800);
    }
    
    function showTypingIndicator() {
        const typingEl = document.createElement('div');
        typingEl.className = 'typing-indicator';
        typingEl.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;
        
        if (chatbotMessages) {
            chatbotMessages.appendChild(typingEl);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }
    }
    
    function hideTypingIndicator() {
        const typingIndicator = document.querySelector('.typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    function getBotResponse(message) {
        const lowercaseMessage = message.toLowerCase();
        
        // Recherche de mots-clés
        for (const [keyword, response] of Object.entries(botResponses)) {
            if (lowercaseMessage.includes(keyword)) {
                return response;
            }
        }
        
        // Réponse par défaut
        return 'Je ne suis pas sûr de comprendre votre question. Pouvez-vous me demander des informations sur mes services, tarifs, portfolio ou comment me contacter ?';
    }
}

// ===== FORMULAIRE DE CONTACT =====
function initializeContactForm() {
    const contactForm = document.querySelector('#contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactForm);
        
        // Validation en temps réel
        const inputs = contactForm.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => clearErrors(input));
        });
    }
}

function handleContactForm(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Validation
    if (!validateForm(form)) {
        return;
    }
    
    // Simuler l'envoi
    submitBtn.textContent = 'Envoi en cours...';
    submitBtn.disabled = true;
    
    setTimeout(() => {
        // Simuler succès
        showFormSuccess(form);
        form.reset();
        submitBtn.textContent = 'Envoyer le message';
        submitBtn.disabled = false;
    }, 2000);
}

function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let errorMessage = '';
    
    // Validation requis
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Ce champ est requis';
    }
    // Validation email
    else if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Adresse email invalide';
        }
    }
    // Validation téléphone
    else if (type === 'tel' && value) {
        const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            isValid = false;
            errorMessage = 'Numéro de téléphone invalide';
        }
    }
    
    showFieldError(field, errorMessage);
    return isValid;
}

function showFieldError(field, message) {
    clearErrors(field);
    
    if (message) {
        field.classList.add('error');
        const errorEl = document.createElement('div');
        errorEl.className = 'form-error';
        errorEl.textContent = message;
        field.parentNode.appendChild(errorEl);
    }
}

function clearErrors(field) {
    field.classList.remove('error');
    const existingError = field.parentNode.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
}

function showFormSuccess(form) {
    const successEl = document.createElement('div');
    successEl.className = 'form-success';
    successEl.textContent = 'Message envoyé avec succès ! Je vous répondrai dans les plus brefs délais.';
    successEl.style.cssText = `
        background: #10b981;
        color: white;
        padding: 1rem;
        border-radius: 10px;
        margin-top: 1rem;
        text-align: center;
        animation: slideInUp 0.5s ease;
    `;
    
    form.appendChild(successEl);
    
    setTimeout(() => {
        successEl.remove();
    }, 5000);
}

// ===== BOUTON RETOUR EN HAUT =====
function initializeBackToTop() {
    const backToTopBtn = document.querySelector('.back-to-top');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// ===== SCROLL FLUIDE =====
function initializeSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            
            const targetId = link.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 100;
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ===== FONCTIONS UTILITAIRES =====

// Debounce function pour optimiser les performances
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function pour les événements de scroll
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Animation au scroll optimisée
const optimizedScrollHandler = throttle(() => {
    updateScrollProgress();
    updateActiveLink();
}, 16);

window.addEventListener('scroll', optimizedScrollHandler);

// Gestion du redimensionnement de fenêtre
const optimizedResizeHandler = debounce(() => {
    // Recalculer les animations si nécessaire
    initializeAnimations();
}, 250);

window.addEventListener('resize', optimizedResizeHandler);

// Export des fonctions pour utilisation globale
window.toggleTheme = toggleTheme;
window.toggleMobileMenu = toggleMobileMenu;
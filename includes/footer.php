<!-- footer.php -->
                </div> <!-- End of col-lg-9 -->
            </div> <!-- End of row -->
        </div> <!-- End of container -->
    </div> <!-- End of main-content -->

    <!-- Enhanced Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-heartbeat fa-2x me-3" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                        <h3 class="navbar-brand m-0"><?= SITE_NAME ?></h3>
                    </div>
                    <p class="mt-3" style="color: rgba(255,255,255,0.7); line-height: 1.6;">
                        Transforming healthcare through innovative technology that connects patients with quality medical care. 
                        Your health and well-being are our top priorities.
                    </p>
                    <div class="social-links mt-4">
                        <a href="#" class="text-white me-3" style="opacity: 0.7; transition: var(--transition);">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                        <a href="#" class="text-white me-3" style="opacity: 0.7; transition: var(--transition);">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <a href="#" class="text-white me-3" style="opacity: 0.7; transition: var(--transition);">
                            <i class="fab fa-linkedin-in fa-lg"></i>
                        </a>
                        <a href="#" class="text-white" style="opacity: 0.7; transition: var(--transition);">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 mb-4">
                    <div class="footer-links">
                        <h5 class="text-white mb-3 fw-bold">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.7); transition: var(--transition);">Dashboard</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.7); transition: var(--transition);">Appointments</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.7); transition: var(--transition);">Find Doctors</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.7); transition: var(--transition);">Medical Records</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 mb-4">
                    <div class="footer-links">
                        <h5 class="text-white mb-3 fw-bold">Medical Specialties</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.7); transition: var(--transition);">Cardiology</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.7); transition: var(--transition);">Dermatology</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.7); transition: var(--transition);">Pediatrics</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.7); transition: var(--transition);">Orthopedics</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.7); transition: var(--transition);">Neurology</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 mb-4">
                    <div class="footer-links">
                        <h5 class="text-white mb-3 fw-bold">Contact Info</h5>
                        <ul class="list-unstyled">
                            <li class="mb-3 d-flex align-items-start">
                                <i class="fas fa-map-marker-alt me-3 mt-1" style="color: var(--primary);"></i>
                                <span style="color: rgba(255,255,255,0.7);">123 Healthcare Ave, Medical City, MC 12345</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-phone me-3" style="color: var(--primary);"></i>
                                <span style="color: rgba(255,255,255,0.7);">(555) 123-4567</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-envelope me-3" style="color: var(--primary);"></i>
                                <span style="color: rgba(255,255,255,0.7);">info@<?= strtolower(SITE_NAME) ?>.com</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-clock me-3" style="color: var(--primary);"></i>
                                <span style="color: rgba(255,255,255,0.7);">24/7 Emergency Support</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="copyright pt-4 mt-4 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0" style="color: rgba(255,255,255,0.6);">
                            © <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="footer-links">
                            <a href="#" class="text-decoration-none me-3" style="color: rgba(255,255,255,0.6); font-size: 0.9rem; transition: var(--transition);">Privacy Policy</a>
                            <a href="#" class="text-decoration-none me-3" style="color: rgba(255,255,255,0.6); font-size: 0.9rem; transition: var(--transition);">Terms of Service</a>
                            <a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.6); font-size: 0.9rem; transition: var(--transition);">HIPAA Compliance</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap & JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
    
    <script>
        // Enhanced interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to footer links
            const footerLinks = document.querySelectorAll('footer a');
            footerLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.color = 'white';
                    this.style.opacity = '1';
                });
                
                link.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('social-links')) {
                        this.style.color = 'rgba(255,255,255,0.7)';
                    }
                    this.style.opacity = '0.7';
                });
            });

            // Social links specific styling
            const socialLinks = document.querySelectorAll('.social-links a');
            socialLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.opacity = '1';
                    this.style.transform = 'translateY(-3px)';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.opacity = '0.7';
                    this.style.transform = 'translateY(0)';
                });
            });

            // Active navigation highlighting
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-sidebar .nav-link');
            
            navLinks.forEach(link => {
                const linkHref = link.getAttribute('href');
                if (linkHref && linkHref.includes(currentPage)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
<?php
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = rtrim($config['app']['base_url'], '/');
?>
<div class="landing">
    <canvas id="landing-particles" class="landing-particles" aria-hidden="true"></canvas>
    <header class="landing-header">
        <div class="landing-brand">
            <div class="landing-logo landing-logo-image"></div>
            <div class="landing-name">SMSNest</div>
        </div>
        <nav class="landing-nav">
            <a class="landing-link" href="#features">Features</a>
            <a class="landing-link" href="#flow">Flow</a>
            <a class="landing-link" href="#pricing">Pricing</a>
            <a class="landing-link" href="#reviews">Reviews</a>
        </nav>
        <div class="landing-actions">
            <button class="landing-theme" type="button" aria-label="Toggle theme">
                <span class="iconify" data-icon="tabler:moon"></span>
            </button>
            <a class="btn primary" href="<?php echo $baseUrl; ?>/login">Login</a>
        </div>
    </header>

    <section class="landing-hero">
        <div class="landing-hero-inner">
            <div class="hero-tag">Trusted SMS verification hub</div>
            <h1><span>Temporary phone numbers</span><br>for SMS verification</h1>
            <p>
                Receive SMS verification codes instantly with secure, reliable virtual numbers.
                Perfect for WhatsApp, Telegram, Facebook, and more.
            </p>
            <div class="hero-actions">
                <a class="btn primary" href="<?php echo $baseUrl; ?>/register">Get Started Now</a>
                <a class="btn" href="#pricing">View Pricing</a>
            </div>
            <div class="hero-metrics">
                <div class="metric">
                    <div class="metric-value">150+</div>
                    <div class="metric-label">Countries</div>
                </div>
                <div class="metric">
                    <div class="metric-value">99.95%</div>
                    <div class="metric-label">Delivery</div>
                </div>
                <div class="metric">
                    <div class="metric-value">24/7</div>
                    <div class="metric-label">Routing</div>
                </div>
            </div>
            <div class="hero-trust">
                <span>Popular services</span>
                <div class="trust-marquee">
                    <div class="trust-track">
                        <span class="trust-pill">Facebook</span>
                        <span class="trust-pill">Instagram</span>
                        <span class="trust-pill">Telegram</span>
                        <span class="trust-pill">WhatsApp</span>
                        <span class="trust-pill">TikTok</span>
                        <span class="trust-pill">Google</span>
                        <span class="trust-pill">Apple</span>
                        <span class="trust-pill">Discord</span>
                        <span class="trust-pill">X</span>
                        <span class="trust-pill">Gmail</span>
                        <span class="trust-pill">YouTube</span>
                        <span class="trust-pill">LinkedIn</span>
                        <span class="trust-pill">WeChat</span>
                        <span class="trust-pill">LINE</span>
                        <span class="trust-pill">Uber</span>
                        <span class="trust-pill">Netflix</span>
                        <span class="trust-pill">PayPal</span>
                    </div>
                    <div class="trust-track" aria-hidden="true">
                        <span class="trust-pill">Facebook</span>
                        <span class="trust-pill">Instagram</span>
                        <span class="trust-pill">Telegram</span>
                        <span class="trust-pill">WhatsApp</span>
                        <span class="trust-pill">TikTok</span>
                        <span class="trust-pill">Google</span>
                        <span class="trust-pill">Apple</span>
                        <span class="trust-pill">Discord</span>
                        <span class="trust-pill">X</span>
                        <span class="trust-pill">Gmail</span>
                        <span class="trust-pill">YouTube</span>
                        <span class="trust-pill">LinkedIn</span>
                        <span class="trust-pill">WeChat</span>
                        <span class="trust-pill">LINE</span>
                        <span class="trust-pill">Uber</span>
                        <span class="trust-pill">Netflix</span>
                        <span class="trust-pill">PayPal</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-orbit">
            <div class="orbit-ring"></div>
            <div class="orbit-ring orbit-ring-alt"></div>
            <div class="hero-chip">
                <span class="iconify" data-icon="tabler:shield-check"></span>
                Secure & private
            </div>
        </div>
    </section>

    <section id="features" class="landing-section">
        <h2>Why choose SMSNest?</h2>
        <p class="landing-subtitle">
            Experience the best SMS verification service with features designed for convenience and security.
        </p>
        <div class="feature-grid">
            <div class="feature-card">
                <span class="iconify" data-icon="tabler:phone"></span>
                <h3>Temporary numbers</h3>
                <p>Access virtual numbers from 150+ countries with consistent availability, fast routing, and clean delivery paths.</p>
            </div>
            <div class="feature-card">
                <span class="iconify" data-icon="tabler:coin"></span>
                <h3>Transparent pricing</h3>
                <p>Pay only for successful verifications with clear rates, live stock pricing, and no hidden fees.</p>
            </div>
            <div class="feature-card">
                <span class="iconify" data-icon="tabler:rocket"></span>
                <h3>Instant delivery</h3>
                <p>Get your number in seconds with real-time stock and delivery status updates as OTPs arrive.</p>
            </div>
            <div class="feature-card">
                <span class="iconify" data-icon="tabler:lock"></span>
                <h3>Secure platform</h3>
                <p>Privacy-first infrastructure, secure storage, and routing designed to reduce failures and retries.</p>
            </div>
        </div>
    </section>

    <section class="landing-section usecase-section">
        <div class="usecase-header">
            <h2>Built for modern verification flows</h2>
            <p class="landing-subtitle">
                Whether you run campaigns, automate QA, or protect user privacy, SMSNest keeps your stack fast and reliable.
            </p>
        </div>
        <div class="usecase-grid">
            <div class="usecase-card">
                <h3>Growth teams</h3>
                <p>Launch in new regions quickly with a clean inventory of virtual numbers.</p>
                <ul class="usecase-list">
                    <li>Live stock visibility</li>
                    <li>Fast OTP delivery</li>
                    <li>Low-fail routing</li>
                </ul>
            </div>
            <div class="usecase-card">
                <h3>QA & Automation</h3>
                <p>Test flows without burning your real numbers or accounts.</p>
                <ul class="usecase-list">
                    <li>Instant provisioning</li>
                    <li>Reusable test flows</li>
                    <li>API-ready pipeline</li>
                </ul>
            </div>
            <div class="usecase-card">
                <h3>Privacy-first users</h3>
                <p>Keep your real phone number private for any new service.</p>
                <ul class="usecase-list">
                    <li>Disposable numbers</li>
                    <li>One-click cancel</li>
                    <li>Transparent billing</li>
                </ul>
            </div>
        </div>
    </section>

    <section id="flow" class="landing-section flow-section">
        <div class="flow-header">
            <h2>How it works</h2>
            <p class="landing-subtitle">
                Spin up a number, receive the OTP, and keep your main line private.
            </p>
        </div>
        <div class="flow-grid">
            <div class="flow-card">
                <h3>Pick a service</h3>
                <p>Choose the app or site and country. Browse live stock, clear pricing, and the best route for that service.</p>
            </div>
            <div class="flow-card">
                <h3>Get the number</h3>
                <p>Receive a temporary number instantly. Each number comes with clean routing and real-time delivery status.</p>
            </div>
            <div class="flow-card">
                <h3>Verify fast</h3>
                <p>OTP arrives in seconds. Confirm, reuse, or cancel with one click—only pay for successful verifications.</p>
            </div>
        </div>
    </section>

    <section id="pricing" class="landing-section pricing-section">
        <div class="pricing-header">
            <h2>Simple pricing</h2>
            <p class="landing-subtitle">
                Start with a small deposit and scale as you grow.
            </p>
        </div>
        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-badge">Starter</div>
                <div class="pricing-value">$0.12</div>
                <div class="pricing-label">Average per OTP</div>
                <ul class="pricing-list">
                    <li>Instant delivery</li>
                    <li>Live stock updates</li>
                    <li>Cancel anytime</li>
                </ul>
                <a class="btn" href="<?php echo $baseUrl; ?>/register">Try now</a>
            </div>
            <div class="pricing-card featured">
                <div class="pricing-badge">Popular</div>
                <div class="pricing-value">$0.08</div>
                <div class="pricing-label">Bulk discounts</div>
                <ul class="pricing-list">
                    <li>Priority routing</li>
                    <li>Dedicated support</li>
                    <li>API access</li>
                </ul>
                <a class="btn primary" href="<?php echo $baseUrl; ?>/register">Start free</a>
            </div>
            <div class="pricing-card">
                <div class="pricing-badge">Enterprise</div>
                <div class="pricing-value">Custom</div>
                <div class="pricing-label">High volume</div>
                <ul class="pricing-list">
                    <li>Custom SLAs</li>
                    <li>Private routes</li>
                    <li>Account manager</li>
                </ul>
                <a class="btn" href="<?php echo $baseUrl; ?>/contact">Talk to us</a>
            </div>
        </div>
    </section>

    <section id="reviews" class="landing-section review-section">
        <h2>Loved by fast-moving teams</h2>
        <p class="landing-subtitle">
            Operators, founders, and growth teams trust SMSNest every day.
        </p>
        <div class="review-grid">
            <div class="review-card">
                <div class="review-stars">★★★★★</div>
                <p>“Numbers arrive fast and the cancel flow saves us money every day.”</p>
                <div class="review-footer">
                    <div class="review-avatar">H</div>
                    <div class="review-user">Han • Growth Lead</div>
                </div>
            </div>
            <div class="review-card">
                <div class="review-stars">★★★★★</div>
                <p>“Clean routing, solid inventory, and instant delivery for Asia.”</p>
                <div class="review-footer">
                    <div class="review-avatar">M</div>
                    <div class="review-user">Minh • Ops</div>
                </div>
            </div>
            <div class="review-card">
                <div class="review-stars">★★★★★</div>
                <p>“Support is quick and pricing is straightforward.”</p>
                <div class="review-footer">
                    <div class="review-avatar">J</div>
                    <div class="review-user">Jade • Founder</div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section faq-section">
        <h2>Frequently asked questions</h2>
        <div class="faq-grid">
            <div class="faq-card">
                <h3>Do I need a subscription?</h3>
                <p>No. SMSNest is pay-as-you-go. Deposit once and spend as you need.</p>
            </div>
            <div class="faq-card">
                <h3>Which countries are supported?</h3>
                <p>We cover 150+ countries with live stock updates across services.</p>
            </div>
            <div class="faq-card">
                <h3>What if a number doesn’t work?</h3>
                <p>Cancel the request and pick a new number instantly.</p>
            </div>
            <div class="faq-card">
                <h3>Can I use an API?</h3>
                <p>Yes. We provide API endpoints for bulk operations and automation.</p>
            </div>
        </div>
    </section>

    <section class="landing-cta">
        <div>
            <h2>Ready to verify faster?</h2>
            <p>Join SMSNest and start receiving OTPs in seconds.</p>
        </div>
        <div class="cta-actions">
            <a class="btn primary" href="<?php echo $baseUrl; ?>/register">Create account</a>
            <a class="btn" href="<?php echo $baseUrl; ?>/login">Login</a>
        </div>
    </section>
</div>

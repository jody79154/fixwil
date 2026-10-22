<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>WIL-Connect | Home</title>
        <style>
		    * { margin: 0; padding: 0; box-sizing: border-box; }
			
		    body {
                font-family: 'Inter', sans-serif;
                color: #48557a;
                background: #0b1f4d;
                line-height: 1.6;
            }

            h1, h2, h3 {
     			font-family: 'Poppins', sans-serif; 
				color: #0b1f4d; 
			}

            a { text-decoration: none; }

            section { padding: 70px 8%; }
			
		    .hero {
                background: linear-gradient(180deg, #0b1f4d 0%, #14295e 100%);
                color: #fff;
                text-align: center;
                padding: 90px 8% 60px;
                overflow: hidden;
            }

            .hero h1 {
                color: #fff;
                font-size: clamp(32px, 5vw, 52px);
                font-weight: 800;
                max-width: 780px;
                margin: 0 auto 20px;
            }

            .hero h1 .highlight { color: #116ad8; }
			
			.eyebrow {
                display: inline-block;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: 1.5px;
                text-transform: uppercase;
                color: #116ad8;
                margin-bottom: 10px;
            }

            .hero p {
                max-width: 560px;
                margin: 0 auto 34px;
                font-size: 17px;
                color: #c7d3ee;
            }

            .trust-strip{
                /*background: #eef3fb;*/
				background: #f5a623;
				border-bottom: 1px solid #eef3fb;
            }
			
            .trust-strip .wrap{
                display: flex;
				align-items: center;
				justify-content: space-between;
                padding: 16px 45px;
				flex-wrap:wrap;
				gap:18px;
            }
			
            .trust-item{
			    display: flex; 
				align-items: center;
				gap: 10px;
				color: #0b1f4d;
				font-weight: 600;
				font-size: 13.5px;
			}
			
            .trust-item svg{
			    color: #116ad8;
				flex:0 0 auto;
			}
			
            .features { 
			    background: #eef3fb;
				/*text-align: center;*/
				padding: 30px 45px;
			}
			
            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 24px;
                margin: 40px 0;
            }
			
            .card {
                background: #fff;
				border-top: 4px solid #116ad8; 
				border-bottom: 4px solid #116ad8; 
                border-radius: 14px;
                padding: 30px 22px;
                text-align: left;
                transition: box-shadow .2s ease;
            }
			
            .card:hover { 
			    box-shadow: 0 12px 28px rgba(11,31,77,.08); 
			}
			
			
            .card h3 { 
			    /*font-size: 18px; */
				margin-bottom: 8px;
				
				font-family: 'Poppins', sans-serif;
			    font-weight: 800; 
				color: #116ad8;
				font-size: 22px; 
			}
			
            .card p { 
			    font-size: 14.5px; 
				
			}

            .steps-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
                gap: 20px;
                margin: 40px 0;
            }
			
			.steps { 
			    background: #eef3fb;
				padding: 30px 45px;
			}
			
            .step {
				background: #fff;
                border-radius: 14px;
                padding: 40px 32px;
                border-top: 4px solid #f5a623; 
				border-bottom: 4px solid #f5a623; 
                transition: box-shadow .2s ease;
            }
			
			.step:hover {
			    box-shadow: 0 12px 28px rgba(11,31,77,.08); 
			}
			
            .step .num { 
			    font-family: 'Poppins', sans-serif;
			    font-weight: 800; 
				color: #f5a623;
				font-size: 22px; 
			}
			
            .step h3 {
     			font-size: 16px; 
				margin: 8px 0 6px; 
				
				font-family: 'Poppins', sans-serif;
			    font-weight: 800; 
				color: #f5a623;
			}
			
            .step p { 
			    font-size: 13.5px;
				font-weight: 400;
			}
		</style>
	</head>
	<body>
	    <?php include 'wil-navbar.php'; ?>
		<main>
		    <section class="hero">
                <span class="eyebrow">Work Integrated Learning</span>
                <h1>Connecting Students. <span class="highlight">Unlocking Opportunities.</span></h1>
                <p>Find internships, earn verified badges, and turn your learning into real-world experience. All in one place.</p>
            </section>
			
			<div class="trust-strip">
                <div class="wrap">
                    <div class="trust-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
							<path d="M12 2 4 5v6c0 5 3.4 9 8 11 4.6-2 8-6 8-11V5l-8-3z" stroke="currentColor" stroke-width="2"/>
						</svg>
                        Secure &amp; Reliable
                    </div>
                    <div class="trust-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
						    <path d="M7 18a4 4 0 0 1-.5-7.97A5.5 5.5 0 0 1 17 9a4.5 4.5 0 0 1 .5 9H7z" stroke="currentColor" stroke-width="2"/>
						</svg>
                        Cloud Powered (AWS)
                    </div>
                    <div class="trust-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
						    <circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="2"/>
							<path d="M2 20c0-3.3 3-6 7-6s7 2.7 7 6" stroke="currentColor" stroke-width="2"/>
							<circle cx="18" cy="9" r="2.4" stroke="currentColor" stroke-width="2"/>
							<path d="M15.5 20c.2-2.7 2-4.7 4.5-5.4" stroke="currentColor" stroke-width="2"/>
						</svg>
                        Built for Students
                    </div>
                    <div class="trust-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
						    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
							<path d="M3 12h18M12 3c2.5 2.5 3.5 6 3.5 9s-1 6.5-3.5 9c-2.5-2.5-3.5-6-3.5-9S9.5 5.5 12 3z" stroke="currentColor" stroke-width="2"/>
						</svg>
                        Global Opportunities
                    </div>
                </div>
            </div>
			
            <section class="features" id="features">
			    <?php
				    $features = [
                        ["title" => "For Students",  "text" => "Find internships, earn experience, and grow your future."],
                        ["title" => "For Companies",  "text" => "Hire talented, certified students you can already trust."],
                        ["title" => "Fair & Verified", "text" => "Equal opportunity. Verified skills. Real impact."],
                        ["title" => "Badges to Internships", "text" => "From learning, to proof, to real-world experience."],
                    ];
				?>
                <span class="eyebrow">Why WIL-Connect</span>
                <h2>Built for students. Trusted by companies.</h2>

                <div class="features-grid">
                <?php foreach ($features as $f): ?>
                    <div class="card">
                        <h3><?php echo $f['title']; ?></h3>
                        <p><?php echo $f['text']; ?></p>
                    </div>
                <?php endforeach; ?>
                </div>
            </section>

            <section class="steps" id="steps">
			    <?php
				    $steps = [
                        ["num" => "01", "title" => "Find Internships", "text" => "Browse and apply for opportunities from top companies."],
                        ["num" => "02", "title" => "Earn Badges",       "text" => "Complete short courses and earn industry-recognised badges."],
                        ["num" => "03", "title" => "Apply & Track",      "text" => "Apply easily and track every application in real time."],
                        ["num" => "04", "title" => "Stay Notified",      "text" => "Get real-time updates on deadlines and opportunities."],
                        ["num" => "05", "title" => "Grow Your Career",   "text" => "Build skills, gain experience, launch your career."],
                    ];
				?>
                <span class="eyebrow">The Journey</span>
                <h2>From badge to internship, step by step.</h2>

                <div class="steps-row">
                <?php foreach ($steps as $s): ?>
                    <div class="step">
                        <div class="num"><?php echo $s['num']; ?></div>
                        <h3><?php echo $s['title']; ?></h3>
                        <p><?php echo $s['text']; ?></p>
                    </div>
                <?php endforeach; ?>
                </div>
            </section>
		</main>
		<?php include 'footer.php'; ?>
		
	<?php $wilChatBase = ''; require __DIR__ . '/student/chat_widget.php'; ?>
</body>
</html>

<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: blog.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - Pet Adoption Blog</title>
    <link rel="stylesheet" href="blog.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
</head>
<body>
    <!-- Header section -->
    <header>
        <div class="logo">Tails</div>
        <nav>
            <ul>
            <li><a href="../homepage/home.php" >Home</a></li>
        <li><a href="../adoption/adoption_listing.php">Adopt</a></li>
        <li><a href="../adoption/pet_listing.php">List</a></li>
        <li><a href="../aboutus/about.php" >About Us</a></li>
        <li><a href="../blog/blog.php" style="color: #fcfa56;">Blog</a></li>
        <li><a href="../donation/donate.php">Donate</a></li> 
            </ul>
        </nav>
        <div class="login">
            <?php if ($isLoggedIn): ?>
                <a href="?logout=1" class="nav-login">LOG OUT</a>
            <?php else: ?>
                <a href="../modern_login_page/index.php" class="nav-login">LOG IN</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Blog Section -->
    <section class="blog-section">
        <h1><i class="fas fa-paw"></i> Tails Blog: Pet Adoption & Care</h1>
        
        <div class="blog-image">
            <img src="fam.png" alt="Happy family with adopted pet" class="center-image">
        </div>

        <div class="blog-content">
            <div class="blog-topic left">
                <h2><i class="fas fa-heart"></i> Pet Adoption Tips</h2>
                <h3>How to Choose the Right Pet for Your Family</h3>
                <div class="blog-text">
                    <h4>1. Consider Your Living Space</h4>
                    <ul>
                        <li><strong>Apartment vs. House:</strong> Large dogs need more space, while cats or small dogs suit apartments.</li>
                        <li><strong>Outdoor Space:</strong> A yard benefits pets needing exercise, like dogs.</li>
                    </ul>

                    <h4>2. Evaluate Your Lifestyle</h4>
                    <ul>
                        <li><strong>Active:</strong> High-energy dogs like Labradors suit active lifestyles.</li>
                        <li><strong>Relaxed:</strong> Cats or low-energy breeds like Bulldogs suit laid-back lifestyles.</li>
                        <li><strong>Time Commitment:</strong> Dogs need more time for walks and play; cats are more independent.</li>
                    </ul>

                    <h4>3. Family Considerations</h4>
                    <ul>
                        <li><strong>Children:</strong> Some breeds, like Golden Retrievers, are known to be family-friendly.</li>
                        <li><strong>Allergies:</strong> Consider hypoallergenic breeds like Poodles if family members have allergies.</li>
                    </ul>

                    <h4>4. Pet Personality</h4>
                    <ul>
                        <li><strong>Temperament:</strong> Some pets are social, others shy or independent.</li>
                        <li><strong>Energy Levels:</strong> High-energy pets need regular exercise and stimulation.</li>
                    </ul>

                    <h4>5. Long-Term Commitment</h4>
                    <p>Remember, pets are a long-term responsibility. Be prepared for the joy and commitment of pet ownership!</p>
                </div>
                
                <h3>Adopting vs. Buying: Why Adoption is Better</h3>
                <div class="blog-text">
                    <ul>
                        <li><strong>Saving Lives:</strong> Adoption gives homeless pets a second chance.</li>
                        <li><strong>Cost-Effective:</strong> Adoption fees often include vaccinations, microchipping, and spaying/neutering.</li>
                        <li><strong>Supporting Animal Welfare:</strong> Adopting supports organizations dedicated to animal care.</li>
                        <li><strong>Wide Variety:</strong> Shelters offer diverse pets, from puppies to older animals.</li>
                        <li><strong>Fighting Overbreeding:</strong> Adoption reduces demand for puppy mills and unethical breeders.</li>
                    </ul>
                </div>
            </div>

            <div class="image-space">
                <img src="training.jpg" alt="Cat in a box" class="blog-image smaller-image">
            </div>

            <div class="blog-topic right">
                <h2><i class="fas fa-book"></i> Pet Care and Training Guides</h2>
                <h3>First-Time Pet Owner's Guide</h3>
                <div class="blog-text">
                    <ol>
                        <li><strong>Choose Wisely:</strong> Consider lifestyle, space, and time commitment when selecting a pet.</li>
                        <li><strong>Prepare Your Home:</strong> Create a safe space with essentials like bed, bowls, toys, and grooming tools.</li>
                        <li><strong>Healthy Diet:</strong> Provide high-quality food suited for your pet's age, breed, and species.</li>
                        <li><strong>Regular Vet Visits:</strong> Schedule check-ups for vaccinations, flea prevention, and dental care.</li>
                        <li><strong>Training and Socialization:</strong> Teach basic commands and socialize your pet with people and other animals.</li>
                        <li><strong>Patience:</strong> Allow time for your new pet to adjust to their new home.</li>
                    </ol>
                </div>
                
                <h3>Basic Training for Dogs and Cats</h3>
                <div class="blog-text">
                    <h4>1. Positive Reinforcement</h4>
                    <p>Reward good behavior with treats or praise for both dogs and cats.</p>

                    <h4>2. House Training</h4>
                    <ul>
                        <li><strong>Dogs:</strong> Establish a routine for bathroom breaks.</li>
                        <li><strong>Cats:</strong> Provide a clean, quiet area for the litter box.</li>
                    </ul>

                    <h4>3. Basic Commands</h4>
                    <ul>
                        <li><strong>Dogs:</strong> Teach "sit," "stay," and "come" in short, 10-15 minute sessions.</li>
                        <li><strong>Cats:</strong> Use treats to teach simple commands like "come" or "sit."</li>
                    </ul>

                    <h4>4. Leash Training</h4>
                    <ul>
                        <li><strong>Dogs:</strong> Practice walking close and reward good leash behavior.</li>
                        <li><strong>Cats:</strong> Start with a harness indoors, then gradually introduce outdoor walks.</li>
                    </ul>

                    <h4>5. Crate Training (Dogs)</h4>
                    <p>Introduce the crate as a safe space, not punishment. Use treats for positive association.</p>

                    <h4>6. Consistency is Key</h4>
                    <p>Regular, consistent training helps pets learn  faster and maintain good behavior.</p>
                </div>
            </div>

            <div class="image-space">
                <img src="health.jpg" alt="Dogs playing on the beach" class="blog-image smaller-image">
            </div>

            <div class="blog-topic left">
                <h2><i class="fas fa-heartbeat"></i> Pet Health and Well-being</h2>
                <h3>Common Pet Health Issues and Prevention</h3>
                <div class="blog-text">
                    <ul>
                        <li><strong>Obesity:</strong> Balance diet and exercise to prevent diabetes, heart disease, and joint problems.</li>
                        <li><strong>Dental Problems:</strong> Regular brushing and dental check-ups prevent plaque and gum disease.</li>
                        <li><strong>Parasites:</strong> Use vet-recommended treatments for fleas, ticks, and worms.</li>
                        <li><strong>Skin Allergies:</strong> Identify and avoid allergens, keep the environment clean.</li>
                        <li><strong>Ear Infections:</strong> Clean ears regularly, especially for floppy-eared dogs.</li>
                        <li><strong>Vaccinations:</strong> Stay up-to-date with recommended vaccination schedules.</li>
                    </ul>
                </div>
                
                <h3>Spotting Signs of Illness in Your Pet</h3>
                <div class="blog-text">
                    <ul>
                        <li><strong>Appetite Changes:</strong> Sudden loss or increase in appetite.</li>
                        <li><strong>Lethargy:</strong> Unusual tiredness or lack of energy.</li>
                        <li><strong>Digestive Issues:</strong> Frequent vomiting or changes in stool consistency.</li>
                        <li><strong>Thirst/Urination:</strong> Excessive drinking or frequent urination.</li>
                        <li><strong>Respiratory Problems:</strong> Persistent coughing or sneezing.</li>
                        <li><strong>Mobility Issues:</strong> Limping or difficulty moving.</li>
                        <li><strong>Behavioral Changes:</strong> Increased aggression, hiding, or depression.</li>
                        <li><strong>Coat Condition:</strong> Dull fur, excessive shedding, or scratching.</li>
                        <li><strong>Unusual Odors:</strong> Bad breath or strange smells from ears or skin.</li>
                        <li><strong>Weight Changes:</strong> Unexplained weight gain or loss.</li>
                    </ul>
                </div>
            </div>

            <div class="image-space">
                <img src="summer.jpg" alt="Veterinarian examining a dog" class="blog-image smaller-image">
            </div>

            <div class="blog-topic right">
                <h2><i class="fas fa-sun"></i> Seasonal Pet Care</h2>
                <h3>Summer Safety Tips for Pets</h3>
                <div class="blog-text">
                    <ol>
                        <li><strong>Hydration:</strong> Always provide fresh water to prevent dehydration.</li>
                        <li><strong>Hot Surfaces:</strong> Avoid hot pavements that can burn paws.</li>
                        <li><strong>Car Safety:</strong> Never leave pets in hot cars, even for a few minutes.</li>
                        <li><strong>Shade and Ventilation:</strong> Ensure access to cool, shaded areas.</li>
                        <li><strong>Overheating Signs:</strong> Watch for excessive panting, drooling, or lethargy.</li>
                    </ol>
                </div>
                
                <h3>Winter Care for Your Furry Friends</h3>
                <div class="blog-text">
                    <ol>
                        <li><strong>Warmth:</strong> Limit outdoor time in freezing weather; consider pet sweaters.</li>
                        <li><strong>Paw Protection:</strong> Use pet-safe booties or wipe paws after walks to remove ice and salt.</li>
                        <li><strong>Dryness:</strong> Dry wet fur and paws to prevent chills and skin issues.</li>
                        <li><strong>Nutrition:</strong> Provide extra food as pets burn more calories staying warm.</li>
                        <li><strong>Hypothermia Watch:</strong> Monitor for shivering, lethargy, or weakness in cold weather.</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="image-space">
            <img src="psychology.jpg" alt="Cat looking curious" class="blog-image smaller-image">
        </div>

        <div class="blog-topic left">
            <h2><i class="fas fa-brain"></i> Understanding Pet Psychology</h2>
            <h3>The Emotional World of Pets</h3>
            <div class="blog-text">
                <p>Understanding pet psychology is crucial for building a strong bond with your furry friend. Here are some key insights:</p>
                <ul>
                    <li><strong>Emotions:</strong> Pets experience a range of emotions, including joy, fear, anxiety, and love.</li>
                    <li><strong>Communication:</strong> They communicate through body language, vocalizations, and behavior.</li>
                    <li><strong>Social Needs:</strong> Most pets are social animals and require regular interaction and companionship.</li>
                    <li><strong>Environmental Sensitivity:</strong> Changes in their environment can significantly impact their behavior and well-being.</li>
                </ul>
            </div>
            
            <h3>Common Behavioral Issues and Solutions</h3>
            <div class="blog-text">
                <h4>1. Separation Anxiety</h4>
                <ul>
                    <li><strong>Signs:</strong> Excessive barking, destructive behavior when left alone.</li>
                    <li><strong>Solutions:</strong> Gradual desensitization to departures, providing engaging toys.</li>
                </ul>

                <h4>2. Aggression</h4>
                <ul>
                    <li><strong>Causes:</strong> Fear, territoriality, or past trauma.</li>
                    <li><strong>Solutions:</strong> Professional training, socialization, and identifying triggers.</li>
                </ul>

                <h4>3. Excessive Barking or Meowing</h4>
                <ul>
                    <li><strong>Reasons:</strong> Attention-seeking, boredom, or alerting to perceived threats.</li>
                    <li><strong>Solutions:</strong> Consistent training, increased exercise, and mental stimulation.</li>
                </ul>

                <h4>4. Inappropriate Elimination</h4>
                <ul>
                    <li><strong>Causes:</strong> Medical issues, stress, or inadequate training.</li>
                    <li><strong>Solutions:</strong> Veterinary check-up, consistent routines, and positive reinforcement.</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <h2>Tails</h2>
            </div>
            <div class="footer-links">
       
            <a href="../homepage/home.php">Home</a>
            <a href="../aboutus/about.php">About Us</a>
            <a href="../adoption/adoption_listing.php">Adopt</a>
            <a href="../blog/blog.php">Blog</a>
            <a href="../FAQ/index.php">FAQ</a>
            <a href="../feedback/feedback.php">Feedback</a>
            </div>
            <div class="footer-socials">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Tails. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
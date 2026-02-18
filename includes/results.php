<!-- ============================================
     includes/results.php
     Nutrition targets + weekly schedule output
     (rendered by js/plan.js after form submit)
     ============================================ -->

<div id="results">

    <!-- Header + tags -->
    <div class="results-header">
        <div class="section-eyebrow">// YOUR PERSONALIZED PLAN IS READY</div>
        <h2 class="results-title">YOUR <span>PLAN</span></h2>
        <div class="results-tags" id="resultsTags">
            <!-- Tags injected by plan.js: goal / experience / workout type -->
        </div>
    </div>

    <!-- Daily Nutrition Targets -->
    <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;letter-spacing:0.1em;margin-bottom:20px;">
        DAILY <span style="color:var(--accent)">NUTRITION</span> TARGETS
    </h3>
    <div class="nutrition-grid" id="nutritionGrid">
        <!-- Cards injected by plan.js: calories / protein / carbs / fats -->
    </div>

    <!-- Weekly Workout Schedule -->
    <h3 class="schedule-title">WEEKLY <span>WORKOUT</span> SCHEDULE</h3>
    <div id="scheduleGrid">
        <!-- Day cards injected by plan.js -->
    </div>

    <!-- Rebuild plan -->
    <button class="btn-restart" onclick="restartForm()">↩ Rebuild My Plan</button>

</div>

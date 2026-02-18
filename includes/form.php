<!-- ============================================
     includes/form.php
     Multi-step profile input form
     (shown after login, hidden after plan generated)
     ============================================ -->

<div class="form-section" id="formSection">
    <div class="section-eyebrow">// BUILD YOUR PLAN</div>
    <h2 class="section-title">YOUR <span>PROFILE</span></h2>
    <p class="section-sub">Tell us about yourself and we'll generate a precision-crafted plan.</p>

    <!-- Step progress dots -->
    <div class="step-indicator" id="stepIndicator">
        <div class="step-dot active"></div>
        <div class="step-dot"></div>
        <div class="step-dot"></div>
    </div>

    <!-- ======== STEP 1: Body Stats ======== -->
    <div class="step-form active" id="step1">
        <h3 class="step-heading">STEP 01 — <span>BODY STATS</span></h3>

        <label class="field-label">Gender</label>
        <div class="options-grid cols-3" style="margin-bottom:28px;">
            <label class="option-card" onclick="selectOpt(this,'gender','male')">
                <input type="radio" name="gender" value="male">
                <div class="option-icon">♂</div>
                <div class="option-name">Male</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'gender','female')">
                <input type="radio" name="gender" value="female">
                <div class="option-icon">♀</div>
                <div class="option-name">Female</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'gender','other')">
                <input type="radio" name="gender" value="other">
                <div class="option-icon">⚧</div>
                <div class="option-name">Other</div>
            </label>
        </div>

        <div class="fields-row">
            <div>
                <label class="field-label">Age</label>
                <input type="number" class="form-control" id="age" placeholder="e.g. 25" min="13" max="80">
            </div>
            <div>
                <label class="field-label">Weight</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="weight" placeholder="e.g. 70" min="1">
                    <select class="unit-select" id="weightUnit">
                        <option value="kg">kg</option>
                        <option value="lbs">lbs</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="fields-row">
            <div>
                <label class="field-label">Height</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="height" placeholder="e.g. 175" min="1">
                    <select class="unit-select" id="heightUnit">
                        <option value="cm">cm</option>
                        <option value="inches">inches</option>
                        <option value="ft">ft</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="step-nav">
            <span></span>
            <button class="btn-next" onclick="nextStep(1)">NEXT STEP →</button>
        </div>
    </div>

    <!-- ======== STEP 2: Experience + Goal ======== -->
    <div class="step-form" id="step2">
        <h3 class="step-heading">STEP 02 — <span>EXPERIENCE &amp; GOAL</span></h3>

        <label class="field-label" style="margin-bottom:12px;">Experience Level</label>
        <div class="options-grid cols-3" style="margin-bottom:32px;">
            <label class="option-card" onclick="selectOpt(this,'experience','beginner')">
                <input type="radio" name="experience" value="beginner">
                <div class="option-icon">🌱</div>
                <div class="option-name">Beginner</div>
                <div class="option-desc">0–1 year training</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'experience','intermediate')">
                <input type="radio" name="experience" value="intermediate">
                <div class="option-icon">⚡</div>
                <div class="option-name">Intermediate</div>
                <div class="option-desc">1–3 years training</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'experience','expert')">
                <input type="radio" name="experience" value="expert">
                <div class="option-icon">🔥</div>
                <div class="option-name">Expert</div>
                <div class="option-desc">3+ years training</div>
            </label>
        </div>

        <label class="field-label" style="margin-bottom:12px;">Your Goal</label>
        <div class="options-grid cols-2" style="margin-bottom:32px;">
            <label class="option-card" onclick="selectOpt(this,'goal','bulking')">
                <input type="radio" name="goal" value="bulking">
                <div class="option-icon">💪</div>
                <div class="option-name">Bulking</div>
                <div class="option-desc">Build muscle mass &amp; strength</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'goal','cutting')">
                <input type="radio" name="goal" value="cutting">
                <div class="option-icon">🔪</div>
                <div class="option-name">Cutting</div>
                <div class="option-desc">Lose fat, stay lean</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'goal','endurance')">
                <input type="radio" name="goal" value="endurance">
                <div class="option-icon">🏃</div>
                <div class="option-name">Endurance</div>
                <div class="option-desc">Stamina &amp; cardio fitness</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'goal','general_fitness')">
                <input type="radio" name="goal" value="general_fitness">
                <div class="option-icon">⚖️</div>
                <div class="option-name">General Fitness</div>
                <div class="option-desc">Balanced health &amp; wellness</div>
            </label>
        </div>

        <div class="step-nav">
            <button class="btn-back" onclick="prevStep(2)">← Back</button>
            <button class="btn-next" onclick="nextStep(2)">NEXT STEP →</button>
        </div>
    </div>

    <!-- ======== STEP 3: Metabolism + Workout Type ======== -->
    <div class="step-form" id="step3">
        <h3 class="step-heading">STEP 03 — <span>METABOLISM &amp; TRAINING</span></h3>

        <label class="field-label" style="margin-bottom:12px;">Metabolism Type</label>
        <div class="options-grid cols-3" style="margin-bottom:32px;">
            <label class="option-card" onclick="selectOpt(this,'metabolism','fast')">
                <input type="radio" name="metabolism" value="fast">
                <div class="option-icon">⚡</div>
                <div class="option-name">Fast</div>
                <div class="option-desc">Hard to gain weight (+250 cal)</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'metabolism','moderate')">
                <input type="radio" name="metabolism" value="moderate">
                <div class="option-icon">⚖️</div>
                <div class="option-name">Moderate</div>
                <div class="option-desc">Average calorie needs</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'metabolism','slow')">
                <input type="radio" name="metabolism" value="slow">
                <div class="option-icon">🐢</div>
                <div class="option-name">Slow</div>
                <div class="option-desc">Easy to gain weight (−150 cal)</div>
            </label>
        </div>

        <label class="field-label" style="margin-bottom:12px;">Workout Type</label>
        <div class="options-grid cols-2" style="margin-bottom:32px;">
            <label class="option-card" onclick="selectOpt(this,'workoutType','gym')">
                <input type="radio" name="workoutType" value="gym">
                <div class="option-icon">🏋️</div>
                <div class="option-name">Gym Workout</div>
                <div class="option-desc">Full equipment access</div>
            </label>
            <label class="option-card" onclick="selectOpt(this,'workoutType','home')">
                <input type="radio" name="workoutType" value="home">
                <div class="option-icon">🏠</div>
                <div class="option-name">Home Workout</div>
                <div class="option-desc">Bodyweight &amp; minimal equipment</div>
            </label>
        </div>

        <div class="step-nav">
            <button class="btn-back" onclick="prevStep(3)">← Back</button>
            <button class="btn-generate" onclick="generatePlan()">
                ⚡ GENERATE MY PLAN
            </button>
        </div>
    </div>
</div>

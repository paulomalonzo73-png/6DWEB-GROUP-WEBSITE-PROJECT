<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

require_once 'php/config.php';

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'] ?? 0;

$success_msg = '';
$error_msg   = '';

// Load existing profile
$conn = getDBConnection();
$s = $conn->prepare("SELECT * FROM user_profiles WHERE user_id=? ORDER BY id DESC LIMIT 1");
$s->bind_param("i", $user_id);
$s->execute();
$profile = $s->get_result()->fetch_assoc();
$conn->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gender       = trim($_POST['gender']       ?? '');
    $age          = (int)   ($_POST['age']       ?? 0);
    $weight       = (float) ($_POST['weight']    ?? 0);
    $weight_unit  = trim($_POST['weight_unit']   ?? 'kg');
    $height       = (float) ($_POST['height']    ?? 0);
    $height_unit  = trim($_POST['height_unit']   ?? 'cm');
    $experience   = trim($_POST['experience']    ?? '');
    $goal         = trim($_POST['goal']          ?? '');
    $metabolism   = trim($_POST['metabolism']    ?? '');
    $workout_type = trim($_POST['workout_type']  ?? '');

    // Validation
    if (!in_array($gender,       ['male','female','other']))                          { $error_msg = 'Invalid gender.'; }
    elseif ($age < 13 || $age > 80)                                                   { $error_msg = 'Age must be 13–80.'; }
    elseif ($weight <= 0 || $weight > 500)                                            { $error_msg = 'Enter a valid weight.'; }
    elseif ($height <= 0 || $height > 300)                                            { $error_msg = 'Enter a valid height.'; }
    elseif (!in_array($weight_unit,  ['kg','lbs']))                                   { $error_msg = 'Invalid weight unit.'; }
    elseif (!in_array($height_unit,  ['cm','inches','ft']))                           { $error_msg = 'Invalid height unit.'; }
    elseif (!in_array($experience,   ['beginner','intermediate','expert']))           { $error_msg = 'Invalid experience.'; }
    elseif (!in_array($goal,         ['bulking','cutting','endurance','general_fitness'])) { $error_msg = 'Invalid goal.'; }
    elseif (!in_array($metabolism,   ['fast','moderate','slow']))                     { $error_msg = 'Invalid metabolism.'; }
    elseif (!in_array($workout_type, ['gym','home']))                                 { $error_msg = 'Invalid workout type.'; }
    else {
        $conn = getDBConnection();

        if ($profile) {
            // UPDATE — 11 fields + WHERE user_id
            // Types: s i d s d s s s s s i
            $upd = $conn->prepare(
                "UPDATE user_profiles
                 SET gender=?, age=?, weight=?, weight_unit=?,
                     height=?, height_unit=?, experience=?,
                     goal=?, metabolism=?, workout_type=?
                 WHERE user_id=?"
            );
            $upd->bind_param("sidsdssssi",
                $gender, $age, $weight, $weight_unit,
                $height, $height_unit, $experience,
                $goal, $metabolism, $workout_type,
                $user_id
            );
            $upd->execute();
        } else {
            // INSERT — 11 fields
            // Types: i s i d s d s s s s s
            $ins = $conn->prepare(
                "INSERT INTO user_profiles
                 (user_id, gender, age, weight, weight_unit,
                  height, height_unit, experience, goal, metabolism, workout_type)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $ins->bind_param("isidsdssss",
                $user_id, $gender, $age, $weight, $weight_unit,
                $height, $height_unit, $experience, $goal,
                $metabolism, $workout_type
            );
            $ins->execute();
        }

        $conn->close();
        $success_msg = 'Profile updated successfully!';

        // Reload profile
        $conn2 = getDBConnection();
        $s2 = $conn2->prepare("SELECT * FROM user_profiles WHERE user_id=? ORDER BY id DESC LIMIT 1");
        $s2->bind_param("i", $user_id);
        $s2->execute();
        $profile = $s2->get_result()->fetch_assoc();
        $conn2->close();
    }
}

function sel($current, $value) { return $current === $value ? 'selected' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMITLESS — Edit Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        body { padding-top:80px; }
        .wrapper { max-width:760px; margin:0 auto; padding:48px 32px; }
        .page-eyebrow { font-family:'DM Mono',monospace; font-size:0.7rem; color:var(--accent); letter-spacing:0.3em; text-transform:uppercase; margin-bottom:8px; }
        .page-title { font-family:'Bebas Neue',sans-serif; font-size:3rem; margin-bottom:32px; }
        .page-title span { color:var(--accent); }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
        .form-group { margin-bottom:20px; }
        .form-label { display:block; font-family:'DM Mono',monospace; font-size:0.7rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--muted); margin-bottom:8px; }
        .form-control, .form-select { width:100%; background:var(--bg); border:1px solid var(--border); color:var(--text); padding:14px 16px; font-family:'Space Grotesk',sans-serif; font-size:0.95rem; outline:none; transition:border-color 0.2s; }
        .form-control:focus, .form-select:focus { border-color:var(--accent); }
        .form-select option { background:#111; }
        .section-divider { font-family:'DM Mono',monospace; font-size:0.7rem; color:var(--muted); letter-spacing:0.2em; text-transform:uppercase; border-top:1px solid var(--border); padding-top:24px; margin:32px 0 20px; }
        .btn-save { background:var(--accent); color:#000; border:none; padding:16px 48px; font-family:'Bebas Neue',sans-serif; font-size:1.2rem; letter-spacing:0.1em; cursor:pointer; transition:all 0.2s; margin-top:8px; }
        .btn-save:hover { background:#fff; transform:translateY(-1px); }
        .msg { padding:12px 16px; margin-bottom:24px; font-size:0.85rem; border-left:3px solid; }
        .msg-success { background:rgba(0,255,136,0.08); border-color:var(--success); color:var(--success); }
        .msg-error   { background:rgba(255,59,59,0.08);  border-color:var(--danger);  color:#ff8888; }
        @media(max-width:600px){ .form-row{ grid-template-columns:1fr; } }
    </style>
</head>
<body>
<nav>
    <a class="logo" href="app.php"><span>LIMIT</span>LESS</a>
    <div class="nav-actions">
        <span style="font-family:'DM Mono',monospace;font-size:0.8rem;color:<?= $is_admin?'var(--danger)':'var(--accent)' ?>">
            <strong><?= htmlspecialchars($username) ?></strong>
        </span>
        <a href="app.php" class="btn btn-ghost btn-sm">← Back</a>
        <a href="php/logout.php" class="btn btn-ghost btn-sm">Sign Out</a>
    </div>
</nav>

<div class="wrapper">
    <div class="page-eyebrow">// EDIT YOUR STATS</div>
    <h1 class="page-title">EDIT <span>PROFILE</span></h1>

    <?php if ($success_msg): ?><div class="msg msg-success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
    <?php if ($error_msg):   ?><div class="msg msg-error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

    <form method="POST">
        <div class="section-divider">// BODY STATS</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="male"   <?= sel($profile['gender']??'','male')   ?>>Male</option>
                    <option value="female" <?= sel($profile['gender']??'','female') ?>>Female</option>
                    <option value="other"  <?= sel($profile['gender']??'','other')  ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Age</label>
                <input type="number" name="age" class="form-control" placeholder="e.g. 25" min="13" max="80" required
                    value="<?= htmlspecialchars($profile['age']??'') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Weight</label>
                <div style="display:flex;gap:8px">
                    <input type="number" name="weight" class="form-control" placeholder="e.g. 70" min="1" step="0.1" required
                        value="<?= htmlspecialchars($profile['weight']??'') ?>">
                    <select name="weight_unit" class="form-select" style="min-width:80px;width:auto">
                        <option value="kg"  <?= sel($profile['weight_unit']??'kg','kg')  ?>>kg</option>
                        <option value="lbs" <?= sel($profile['weight_unit']??'','lbs')   ?>>lbs</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Height</label>
                <div style="display:flex;gap:8px">
                    <input type="number" name="height" class="form-control" placeholder="e.g. 175" min="1" step="0.1" required
                        value="<?= htmlspecialchars($profile['height']??'') ?>">
                    <select name="height_unit" class="form-select" style="min-width:90px;width:auto">
                        <option value="cm"     <?= sel($profile['height_unit']??'cm','cm')     ?>>cm</option>
                        <option value="inches" <?= sel($profile['height_unit']??'','inches')   ?>>inches</option>
                        <option value="ft"     <?= sel($profile['height_unit']??'','ft')       ?>>ft</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="section-divider">// TRAINING PROFILE</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Experience Level</label>
                <select name="experience" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="beginner"     <?= sel($profile['experience']??'','beginner')     ?>>Beginner</option>
                    <option value="intermediate" <?= sel($profile['experience']??'','intermediate') ?>>Intermediate</option>
                    <option value="expert"       <?= sel($profile['experience']??'','expert')       ?>>Expert</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Goal</label>
                <select name="goal" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="bulking"         <?= sel($profile['goal']??'','bulking')         ?>>Bulking</option>
                    <option value="cutting"         <?= sel($profile['goal']??'','cutting')         ?>>Cutting</option>
                    <option value="endurance"       <?= sel($profile['goal']??'','endurance')       ?>>Endurance</option>
                    <option value="general_fitness" <?= sel($profile['goal']??'','general_fitness') ?>>General Fitness</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Metabolism</label>
                <select name="metabolism" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="fast"     <?= sel($profile['metabolism']??'','fast')     ?>>Fast</option>
                    <option value="moderate" <?= sel($profile['metabolism']??'','moderate') ?>>Moderate</option>
                    <option value="slow"     <?= sel($profile['metabolism']??'','slow')     ?>>Slow</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Workout Type</label>
                <select name="workout_type" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="gym"  <?= sel($profile['workout_type']??'','gym')  ?>>Gym</option>
                    <option value="home" <?= sel($profile['workout_type']??'','home') ?>>Home</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-save">SAVE CHANGES →</button>
    </form>
</div>
</body>
</html>

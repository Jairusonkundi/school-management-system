<style>
    .auth-input:focus {
        border-color: #0087b7 !important;
        box-shadow: 0 0 0 4px rgba(0, 135, 183, 0.14);
        background-color: #ffffff !important;
        outline: none;
    }

    .auth-submit:hover {
        background-color: #007099 !important;
    }
</style>

<section style="display: flex; align-items: center; justify-content: center; min-height: 100vh; width: 100vw; background: linear-gradient(135deg, #0087b7 0%, #1e293b 100%); font-family: 'Inter', sans-serif; padding: 20px;">
    <div style="background: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); width: 100%; max-width: 420px; padding: 40px 35px; border: 1px solid rgba(226, 232, 240, 0.8);">
        <div style="text-align: center;">
            <div style="width: 58px; height: 58px; border-radius: 16px; background: #e0f2fe; color: #0087b7; display: inline-flex; align-items: center; justify-content: center; font-size: 1.9rem;">
                <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; text-align: center; margin-top: 10px; margin-bottom: 4px;">Sunshine Kaseveni Academy</h2>
            <p style="font-size: 0.875rem; color: #64748b; text-align: center; margin-bottom: 30px;">Secure Portal Sign-In</p>
        </div>

        <?php if (!empty($error)): ?>
            <p class="alert" style="margin-bottom: 20px;"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <label for="login-email" style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Email</label>
            <input class="auth-input" id="login-email" type="email" name="email" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #1e293b; transition: all 0.2s ease; margin-bottom: 20px; background-color: #f8fafc;">

            <label for="login-password" style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Password</label>
            <input class="auth-input" id="login-password" type="password" name="password" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #1e293b; transition: all 0.2s ease; margin-bottom: 20px; background-color: #f8fafc;">

            <button class="auth-submit" type="submit" style="width: 100%; padding: 12px; background-color: #0087b7; color: #ffffff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s ease; margin-top: 10px; box-shadow: 0 4px 6px -1px rgba(0, 135, 183, 0.2);">Sign In</button>

            <p style="text-align: center; background-color: #f1f5f9; border-radius: 6px; padding: 8px 12px; font-size: 0.75rem; color: #64748b; margin-top: 25px; border: 1px dashed #cbd5e1;">Demo Access: admin@sunshine.local / password</p>
        </form>
    </div>
</section>

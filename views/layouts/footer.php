<?php if ($user): ?>
            </div>
        </main>
        <footer class="app-footer" style="background-color: #ffffff; border-top: 1px solid #e2e8f0; padding: 15px 30px; text-align: center; color: #64748b; font-size: 0.85rem;">
            &copy; <?= date('Y') ?> Sunshine Kaseveni Academy &middot; Kenyan CBC-ready School Management System
        </footer>
    </div>
</div>
<?php else: ?>
    <footer class="login-footer">
        <p>&copy; <?= date('Y') ?> Sunshine Kaseveni Academy &middot; Kenya CBC-ready School Management System</p>
    </footer>
</div>
<?php endif; ?>
<script src="assets/js/app.js"></script>
</body>
</html>

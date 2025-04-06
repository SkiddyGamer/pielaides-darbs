<div id="overlay"></div>

<div id="authForm">
    <span class="close-btn" onclick="closeModal()">&times;</span>

    <div id="signInForm">
        <h2>Sign In</h2>
        
        
        <form method="post" action="login.php">
            <div class="input-group">
                <p>Email</p>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <p>Password</p>
                <input type="password" name="password" required>
            </div>
            
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
            <input type="submit" class="btn" value="Sign In">
        </form>
        <p>Don't have an account yet?</p>
        <button id="signUpToggle">Sign Up</button>
    </div>

    <div id="signUpForm" style="display:none;">
        <h2>Sign Up</h2>
        
        <form method="post" action="register.php">
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
            <div class="input-group">
                <p>First Name</p>
                <input type="text" name="fName" required>
            </div>
            <div class="input-group">
                <p>Last Name</p>
                <input type="text" name="lName" required>
            </div>
            <div class="input-group">
                <p>Email</p>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <p>Password</p>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <p>Phone Number</p>
                <input type="text" name="pNumber" required>
            </div>
            <input type="submit" class="btn" value="Sign Up" name="signUp">
        </form>
        <p>Already have an account?</p>
        <button id="signInToggle">Sign In</button>
    </div>
</div>

<script>
    const signInButton = document.getElementById("signInButton");
    const signUpButton = document.getElementById("signUpButton");
    const authForm = document.getElementById("authForm");
    const overlay = document.getElementById("overlay");

    signInButton?.addEventListener("click", () => {
        openModal();
        document.getElementById("signInForm").style.display = "block";
        document.getElementById("signUpForm").style.display = "none";
    });

    signUpButton?.addEventListener("click", () => {
        openModal();
        document.getElementById("signInForm").style.display = "none";
        document.getElementById("signUpForm").style.display = "block";
    });

    document.getElementById("signInToggle").addEventListener("click", () => {
        document.getElementById("signInForm").style.display = "block";
        document.getElementById("signUpForm").style.display = "none";
    });

    document.getElementById("signUpToggle").addEventListener("click", () => {
        document.getElementById("signInForm").style.display = "none";
        document.getElementById("signUpForm").style.display = "block";
    });

    function openModal() {
        authForm.style.display = "block";
        overlay.style.display = "block";
        document.body.classList.add("modal-open"); 
    }

    function closeModal() {
        authForm.style.display = "none";
        overlay.style.display = "none";
        document.body.classList.remove("modal-open"); 
    }

    overlay.addEventListener("click", closeModal);
</script>

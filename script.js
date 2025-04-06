signUpButton.addEventListener('click',function(){
    signInForm.style.display="none";
    signUpForm.style.display="block";
})
signInButton.addEventListener('click', function(){
    signInForm.style.display="block";
    signUpForm.style.display="none";
})


        const signInButton = document.getElementById("signInButton");
        const signUpButton = document.getElementById("signUpButton");
        const authForm = document.getElementById("authForm");
        const overlay = document.getElementById("overlay");

   
        signInButton.addEventListener("click", () => {
            openModal();
            document.getElementById("signInForm").style.display = "block";
            document.getElementById("signUpForm").style.display = "none";
        });

        
        signUpButton.addEventListener("click", () => {
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

        
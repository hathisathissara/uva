</div> <!-- End of container-fluid -->
    </div> <!-- End of page-content-wrapper -->
</div> <!-- End of wrapper -->

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Sidebar Toggle Logic -->
<script>
    var el = document.getElementById("wrapper");
    var toggleButton = document.getElementById("sidebarToggle");

    toggleButton.onclick = function () {
        el.classList.toggle("toggled");
    };

    // Optional: Close sidebar automatically on mobile when clicking outside (Advanced)
    // This makes it feel like a real mobile app
    document.addEventListener('click', function(event) {
        var isClickInside = document.getElementById('sidebar-wrapper').contains(event.target);
        var isToggleButton = document.getElementById('sidebarToggle').contains(event.target);
        
        // Only run on mobile (when width < 768)
        if (window.innerWidth < 768) {
            if (!isClickInside && !isToggleButton && el.classList.contains('toggled')) {
                el.classList.remove('toggled');
            }
        }
    });
</script>

</body>
</html>
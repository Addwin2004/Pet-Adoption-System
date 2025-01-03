document.addEventListener("DOMContentLoaded", function () {
  const petsContainer = document.querySelector(".pets-container");
  const filterForm = document.querySelector(".filter-container");

  if (filterForm) {
    filterForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(filterForm);
      const searchParams = new URLSearchParams(formData);

      fetch(`adoption_listing.php?${searchParams.toString()}`, {
        method: "GET",
      })
        .then((response) => response.text())
        .then((html) => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, "text/html");
          const newPetsContainer = doc.querySelector(".pets-container");
          if (newPetsContainer) {
            petsContainer.innerHTML = newPetsContainer.innerHTML;
          }
        })
        .catch((error) => console.error("Error:", error));
    });
  }

  // File input styling
  const fileInput = document.getElementById("file-upload");
  const fileLabel = document.querySelector(".custom-file-upload");

  if (fileInput && fileLabel) {
    fileInput.addEventListener("change", function (e) {
      if (e.target.files.length > 0) {
        fileLabel.textContent = e.target.files[0].name;
      } else {
        fileLabel.textContent = "Choose File";
      }
    });
  }

  // Modal functionality
  window.showPetDetails = function(pet) {
    const modal = document.getElementById('petDetailsModal');
    const modalContent = modal.querySelector('.modal-content');
    
    document.getElementById('modalPetName').textContent = pet.Name;
    document.getElementById('modalPetImage').src = `/miniproject/adoption/uploads/${pet.ImageURL}`;
    document.getElementById('modalPetSpecies').textContent = `Species: ${pet.Species}`;
    document.getElementById('modalPetBreed').textContent = `Breed: ${pet.Breed}`;
    document.getElementById('modalPetAge').textContent = `Age: ${formatAge(pet.Age)}`;
    document.getElementById('modalPetGender').textContent = `Gender: ${pet.Gender}`;
    document.getElementById('modalPetDescription').textContent = `Description: ${pet.Description}`;
    document.getElementById('modalPetOwner').textContent = `Owner: ${pet.OwnerName}`;

    modal.style.display = 'block';
  }

  // Close the modal when clicking on the close button or outside the modal
  window.onclick = function(event) {
    const modal = document.getElementById('petDetailsModal');
    if (event.target == modal || event.target.className == 'close') {
      modal.style.display = 'none';
    }
  }

  // Word count for description
  const descriptionTextarea = document.getElementById('description');
  const wordCount = document.querySelector('.word-count');

  if (descriptionTextarea && wordCount) {
    descriptionTextarea.addEventListener('input', function() {
      const words = this.value.trim().split(/\s+/);
      const count = words.length;
      wordCount.textContent = `${count} / 200 words`;
    });
  }

  // Age input validation
  const ageYearsInput = document.getElementById('age_years');
  const ageMonthsInput = document.getElementById('age_months');
  const listPetForm = document.getElementById('list-pet-form');

  if (ageYearsInput && ageMonthsInput && listPetForm) {
    function validateAge() {
      const years = parseInt(ageYearsInput.value) || 0;
      const months = parseInt(ageMonthsInput.value) || 0;

      if (years === 0 && months === 0) {
        alert("Please enter a valid age.");
        return false;
      }

      if (months > 11) {
        alert("Months should be between 0 and 11.");
        return false;
      }

      return true;
    }

    listPetForm.addEventListener('submit', function(e) {
      if (!validateAge()) {
        e.preventDefault();
      }
    });
  }

  // Helper function to format age
  function formatAge(ageInMonths) {
    const years = Math.floor(ageInMonths / 12);
    const months = ageInMonths % 12;
    
    if (years > 0 && months > 0) {
      return `${years} year(s) ${months} month(s)`;
    } else if (years > 0) {
      return `${years} year(s)`;
    } else {
      return `${months} month(s)`;
    }
  }
});
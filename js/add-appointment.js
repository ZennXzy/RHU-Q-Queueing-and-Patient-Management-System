const formCard = document.getElementById("form-card");

document.getElementById("addBtn").addEventListener("click", () => {
  formCard.classList.add("open");
  console.log('Forn Open');
});
document.getElementById("closeBtn").addEventListener("click", () => {
  formCard.classList.remove("open");
  console.log('Forn Close');
});

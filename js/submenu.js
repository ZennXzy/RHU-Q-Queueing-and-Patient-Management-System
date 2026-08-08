function toggleDropdown(element) {
  // Finds the parent .dropdown-group container
  const group = element.closest(".dropdown-group");
  if (group) {
    group.classList.toggle("open");
  }
}

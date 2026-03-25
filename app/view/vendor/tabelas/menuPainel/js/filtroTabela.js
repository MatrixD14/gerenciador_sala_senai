function filtraTabele(e) {
  if (e) e.preventDefault();
  const form = e.currentTarget || e.target;
  const url = form.getAttribute("action");
  const formData = new FormData(form);
  formData.append("is_search_ajax", "true");
  loadPagePost(url, formData, true);
}

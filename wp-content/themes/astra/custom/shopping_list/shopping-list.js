document.addEventListener("DOMContentLoaded", () => {
  const addItemBtn = document.getElementById("add-item");
  const itemName = document.getElementById("item-name");
  const itemQty = document.getElementById("item-quantity");
  const itemPrice = document.getElementById("item-price");
  const itemsList = document.getElementById("items-list");
  const totalItems = document.getElementById("total-items");
  const totalPrice = document.getElementById("total-price");

  let items = [];

  function updateTotals() {
    totalItems.textContent = items.length;
    let sum = items.reduce((acc, cur) => acc + cur.qty * cur.price, 0);
    totalPrice.textContent = sum.toFixed(2);
  }

  addItemBtn.addEventListener("click", () => {
    const name = itemName.value.trim();
    const qty = parseInt(itemQty.value);
    const price = parseFloat(itemPrice.value);

    if (!name || isNaN(qty) || isNaN(price)) return;

    const item = { name, qty, price };
    items.push(item);

    const li = document.createElement("li");

    // Kreiranje checkboxa
    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.classList.add("item-checkbox");

    // Kada se klikne checkbox, dodaj ili ukloni klasu "completed"
    checkbox.addEventListener("change", () => {
      if (checkbox.checked) {
        li.classList.add("completed");
      } else {
        li.classList.remove("completed");
      }
    });

    li.appendChild(checkbox);

    const text = document.createElement("span");
    // text.textContent = `${name} - ${qty} pcs - ${price.toFixed(2)} €`;
    text.innerHTML = `${name} <span class="sep">-</span> ${qty} pcs <span class="sep">-</span> ${price.toFixed(
      2
    )} €`;
    li.appendChild(text);

    itemsList.appendChild(li);

    itemName.value = "";
    itemQty.value = "";
    itemPrice.value = "";

    updateTotals();
  });

  document.getElementById("save-list").addEventListener("click", () => {
    if (!document.getElementById("list-name").value.trim()) {
      alert("Please enter a name for the shopping list.");
      return;
    }
    if (items.length === 0) {
      alert("Your shopping list is empty.");
      return;
    }
    alert("Shopping list saved successfully! (not implemented yet)");
    // Here you can add functionality to save the list to a database or file
  });
});

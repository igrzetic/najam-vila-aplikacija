document.addEventListener("DOMContentLoaded", () => {
  const addItemBtn = document.getElementById("add-item");
  const itemName = document.getElementById("item-name");
  const itemQty = document.getElementById("item-quantity");
  const itemPrice = document.getElementById("item-price");
  const itemsList = document.getElementById("items-list");
  const totalItems = document.getElementById("total-items");
  const totalPrice = document.getElementById("total-price");
  const listNameInput = document.getElementById("list-name");
  const listDescInput = document.getElementById("list-description");
  const propertyIdEl = document.getElementById("property-id");
  const propertySelectEl = document.getElementById("property-select");
  const propertyIdNewEl = document.getElementById("property_id"); // new select id
  function getPropertyId() {
    if (propertyIdEl) {
      const v = parseInt(propertyIdEl.value, 10);
      if (!isNaN(v) && v > 0) return v;
    }
    if (propertyIdNewEl) {
      const v = parseInt(propertyIdNewEl.value, 10);
      if (!isNaN(v) && v > 0) return v;
    }
    if (propertySelectEl) {
      const v = parseInt(propertySelectEl.value, 10);
      if (!isNaN(v) && v > 0) return v;
    }
    return 0;
  }

  // Provided by wp_localize_script in shopping-list.php
  const saveUrl = (window.ShoppingListConfig && window.ShoppingListConfig.saveUrl) ||
    "/wp-content/themes/astra-child/custom/shopping_list/save-shopping-list.php";
  const userId = (window.ShoppingListConfig && window.ShoppingListConfig.userId) ||
    window.currentUserId || 0;
  const userRole = (window.ShoppingListConfig && window.ShoppingListConfig.userRole) || null;

  // Debug log of session-provided user data
  try {
    console.log("[ShoppingList] Session user:", { userId, userRole });
  } catch (e) {}

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

    const index = items.length;
    const item = { name, qty, price, status: "pending" };
    items.push(item);

    const li = document.createElement("li");
    li.dataset.index = String(index);

    // Kreiranje checkboxa
    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.classList.add("item-checkbox");

    // Kada se klikne checkbox, dodaj ili ukloni klasu "completed" i ažuriraj status u memoriji
    checkbox.addEventListener("change", () => {
      const i = parseInt(li.dataset.index, 10);
      const checked = checkbox.checked;
      if (checked) {
        li.classList.add("completed");
      } else {
        li.classList.remove("completed");
      }
      if (!isNaN(i) && items[i]) {
        items[i].status = checked ? "purchased" : "pending";
      }
    });

    li.appendChild(checkbox); // OVO TREBA UKLONITI

    const text = document.createElement("span");
    text.classList.add("item-text");
    text.innerHTML = `${name} <span class="sep">-</span> ${qty} pcs <span class="sep">-</span> ${price.toFixed(2)} €`;
    li.appendChild(text);

    // Delete button
    const delBtn = document.createElement("button");
    delBtn.type = "button";
    delBtn.className = "delete-btn";
    // delBtn.title = "Remove item";
    delBtn.textContent = "🗑️";
    delBtn.addEventListener("click", () => {
      const i = parseInt(li.dataset.index, 10);
      if (!isNaN(i)) {
        // Remove from data
        items.splice(i, 1);
        // Remove from DOM
        li.remove();
        // Reindex remaining <li> elements
        const lis = itemsList.querySelectorAll("li");
        lis.forEach((node, idx) => {
          node.dataset.index = String(idx);
        });
        // Update totals
        updateTotals();
      }
    });
    li.appendChild(delBtn);

    itemsList.appendChild(li);

    itemName.value = "";
    itemQty.value = "";
    itemPrice.value = "";

    updateTotals();
  });

  const form = document.getElementById("shopping-list-form");
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!listNameInput.value.trim()) {
      alert("Please enter a name for the shopping list.");
      return;
    }
    if (items.length === 0) {
      alert("Your shopping list is empty.");
      return;
    }
    try {
      const pid = getPropertyId();
      if (!pid || isNaN(pid) || pid <= 0) {
        alert("Please select a property.");
        return;
      }

      const payload = {
        user_id: isNaN(userId) ? 0 : userId,
        list_name: listNameInput.value.trim(),
        items: items,
        property_id: pid,
      };

      const res = await fetch(saveUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data && data.success) {
        try { console.log('[ShoppingList] Email recipient:', data.to_email); } catch(e) {}
        // Optionally show a toast, then redirect to dashboard section
        alert(`Shopping list saved! ID: ${data.list_id}`);
        const dashUrl = (window.ShoppingListConfig && window.ShoppingListConfig.dashboardUrl) || "/index.php/admin-dashboard/#shopping-list";
        window.location.href = dashUrl;
      } else {
        console.error(data);
        alert("Saving failed. See console for details.");
      }
    } catch (e) {
      console.error(e);
      alert("Network or server error while saving the list.");
    }
  });
});

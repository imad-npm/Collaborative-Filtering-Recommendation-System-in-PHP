import csv
import random
import os

# === CONFIGURATION ===
NUM_ITEMS = 300       # number of items
NUM_USERS = 600      # number of users
ITEM_PREFIX = "Item"
USER_PREFIX = "User"
MISSING_RATE = 0.4     # 40% missing ratings

OUTPUT_DIR = "datasets"

ITEM_BASED_FILE = os.path.join(OUTPUT_DIR, "data_item_based.csv")
USER_BASED_FILE = os.path.join(OUTPUT_DIR, "data_user_based.csv")

# === GENERATE ITEM-BASED MATRIX (items × users) ===
print(f"📦 Generating item-based dataset: {NUM_ITEMS} items × {NUM_USERS} users ...")

headers_items = ["item"] + [f"{USER_PREFIX}_{i}" for i in range(1, NUM_USERS + 1)]

data_matrix = []  # we’ll reuse this later for the transpose
with open(ITEM_BASED_FILE, "w", newline="") as f:
    writer = csv.writer(f)
    writer.writerow(headers_items)

    for i in range(1, NUM_ITEMS + 1):
        item_name = f"{ITEM_PREFIX}_{i}"
        row = [item_name]
        ratings = []
        for _ in range(NUM_USERS):
            if random.random() < MISSING_RATE:
                ratings.append("")
            else:
                ratings.append(random.randint(1, 5))
        writer.writerow(row + ratings)
        data_matrix.append(ratings)

print(f"✅ Item-based file saved: {ITEM_BASED_FILE}")

# === GENERATE USER-BASED MATRIX (users × items) ===
print(f"🔁 Generating user-based dataset (transpose)...")

headers_users = ["user"] + [f"{ITEM_PREFIX}_{i}" for i in range(1, NUM_ITEMS + 1)]

with open(USER_BASED_FILE, "w", newline="") as f:
    writer = csv.writer(f)
    writer.writerow(headers_users)

    for user_idx in range(NUM_USERS):
        user_name = f"{USER_PREFIX}_{user_idx + 1}"
        row = [user_name]
        # Transpose manually (extract the user_idx-th element from each item row)
        for item_idx in range(NUM_ITEMS):
            row.append(data_matrix[item_idx][user_idx])
        writer.writerow(row)

print(f"✅ User-based file saved: {USER_BASED_FILE}")
print(f"🎉 Done! Both datasets generated inside '{OUTPUT_DIR}/'")

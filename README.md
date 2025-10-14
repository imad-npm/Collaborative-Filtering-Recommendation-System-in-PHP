# Collaborative Filtering Recommendation System

This project provides a PHP implementation of two common collaborative filtering techniques: **User-Based Collaborative Filtering** and **Item-Based Collaborative Filtering**. It is intended for demonstration and experimental purposes .

## How to Use

### Prerequisites

*   PHP 7.4 or higher.

### Running the Demos

Two demo files are provided to showcase each recommendation method.

#### User-Based Demo

To get recommendations for a specific user using the user-based method, run the following command:

```bash
php demo_user_based.php <UserName>
```

For example:

```bash
php demo_user_based.php User_1
```

#### Item-Based Demo

To get recommendations using the item-based method, run:

```bash
php demo_item_based.php <UserName>
```

For example:

```bash
php demo_item_based.php User_1
```

This script will first check for a cached item similarity file (`cache/item_similarities.csv`). If it doesn't exist, it will compute the similarities and save them for future use.

## Implemented Approaches

Both approaches use the **Pearson correlation coefficient** to measure similarity.

### User-Based Collaborative Filtering

This approach finds users with similar rating patterns to the target user and recommends items they have rated highly.

-   **Process**: For a given user, the system iterates through all other users, calculates their similarity, and uses a weighted average of their ratings to predict scores for items the target user has not yet seen.
-   **Data Format**: Expects a **user-item matrix** (`datasets/data_user_based.csv`), where rows are users and columns are items.

### Item-Based Collaborative Filtering

This approach recommends items that are similar to those the target user has already rated positively.

-   **Process**: The system first builds an item-item similarity matrix by calculating the Pearson correlation for every pair of items based on user ratings. This matrix is then used to find items similar to the user's rated items, and a weighted average of the user's ratings is used to predict scores for other items.
-   **Data Format**: Expects an **item-user matrix** (`datasets/data_item_based.csv`), where rows are items and columns are users.
-   **Caching**: The item-item similarity matrix is computationally expensive to create. This implementation includes a caching mechanism: if `cache/item_similarities.csv` exists, it is loaded; otherwise, the matrix is computed and saved for subsequent runs.

## Comparison of Implemented Approaches

| Aspect                   | User-Based Approach                                       | Item-Based Approach                                          |
| ------------------------ | --------------------------------------------------------- | ------------------------------------------------------------ |
| **Performance**          | Similarity calculation is performed in real-time, which can be slow with a large number of users. | The most intensive computation (item-item similarity) is cached, making subsequent recommendations faster. |
| **Scalability**          | Scales poorly as the number of users grows.               | Scales better as the number of items is often more stable than the number of users. |
| **Recommendation Quality** | Can provide more novel and diverse recommendations.       | Recommendations are often more stable and less prone to drastic changes. |

## Limitations

*   **Scalability**: The current implementation is not suitable for large-scale production systems. It processes the entire dataset in memory, which is inefficient for large datasets.
*   **Cold Start Problem**: The system cannot make recommendations for new users with no ratings or for new items that have not been rated.
*   **Data Sparsity**: Performance degrades when the user-item matrix is very sparse, as it becomes difficult to find users or items with overlapping ratings.
*   **Basic Similarity Metric**: Only the Pearson correlation is implemented. Other metrics like Cosine Similarity or Jaccard Similarity might be more suitable for certain datasets.

## Future Perspectives

*   **Database Integration**: Replace the CSV data source with a database for better scalability and data management.
*   **Performance Optimization**: Implement more advanced algorithms (e.g., using matrix factorization techniques like SVD) to handle large and sparse datasets more efficiently.
*   **Hybrid Models**: Combine collaborative filtering with content-based filtering to improve recommendation quality and address the cold start problem.
*   **Evaluation Metrics**: Add metrics like Root Mean Squared Error (RMSE) or Precision and Recall to evaluate the accuracy of the recommendations.
*   **Web Interface**: Build a simple web interface to make the recommendation system more interactive and user-friendly.

## File Descriptions

*   `Recommender.php`: Base class with common functionality.
*   `UserBasedRecommender.php`: Implements the user-based collaborative filtering logic.
*   `ItemBasedRecommender.php`: Implements the item-based collaborative filtering logic.
*   `Similarity.php`: Utility class for calculating the Pearson correlation.
*   `demo_user_based.php` / `demo_item_based.php`: Command-line scripts to demonstrate the recommenders.
*   `utils/SimpleCsv.php`: A simple utility for reading and writing CSV files.
*   `datasets/`: Contains the user-item and item-user data files.
*   `cache/`: Caches the computed item-item similarity matrix.
*   `gen.py`: Python script to generate new datasets.
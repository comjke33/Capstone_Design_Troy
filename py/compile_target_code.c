#include <stdio.h>

int main() {
    int n;
    scanf("%d", &n);

    double scores[1000];
    double max = 0.0;

    for (int i = 0; i < n; i++) {
        scanf("%1f", &scores[i]);
        if (scores[i] > max) {
            max = scores[i];
        }
    }

    double sum = 0.0;
    for (int i = 0; i < n; i++) {
        sum += (scores[i] / max) * 100;
    }

    printf("%.2f\n", sum / n);
    return 0;
}
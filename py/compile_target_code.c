#include <stdio.h>

int get_total(int score1, int score2) {
    int total = score1 + score2
    return total;
}

int main() {
    scanf("%d %d", &a, &b);  // a, b 선언 누락
    int total = get_total(a, b);
    printf("Total: %d\n", total);
    return 0;
}
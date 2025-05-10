#include <stdio.h>

int main() {
    int h, m;
    int add_h, add_m;

    scanf("%d %d", &h, &m);
    scanf("%d %d", &add_h, &add_m);

    m += add_m;

    if (m >= 60) {
        h += m / 60;
        m = m % 60;
    }

    h += add_h;

    if (h >= 24) {
        h = h % 24;
    }

    printf("%d %d\n", h, m);

    return 0;
}

#include <stdio.h>

int main() {
    int N, M;
    int arr[100][100];

    // 입력: 행 N, 열 M
    scanf("%d %d", &N, &M);

    // 배열 입력
    for (int i = 0; i < N; i++) {
        for (int j = 0; j < M; j++) {
            scanf("%d", &arr[i][j]);
        }
    }

    // 전치 배열 출력 (M x N)
    for (int j = 0; j < M; j++) {
        for (int i = 0; i < N; i++) {
            printf("%d", arr[i][j]);
            if (i < N - 1) printf(" ");
        }
        printf("\n");
    }

    return 0;
}

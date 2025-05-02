#include <stdio.h>

void fill_array(int *arr, int size) {
    for (int i = 0; i <= size; i++) {  // ❌ 오류: i <= size는 배열 범위 초과 가능
        arr[i] = i * 2;
    }
}

int main() {
    float data[5];
    int *ptr = data;  // ❌ 오류: float*을 int*에 대입 (포인터 타입 불일치)
    fill_array(ptr, 5);
    return 0;
}
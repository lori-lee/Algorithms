#include <stdio.h>
#include <malloc.h>
#include <stdlib.h>
#include <string.h>

typedef struct _AVLNode {
    int val;
    int deltaH;
    struct _AVLNode *pParent, *pLeft, *pRight;
}AVLNode;
/**
 * LL-Rotate
 *
 **/
AVLNode *LLRotate(AVLNode *pRoot)
{
    AVLNode *pLeftChild, *pLeftChildRight, *pParent;

    pLeftChild = pRoot->pLeft;

    if (pParent = pRoot->pParent) {
        if (pParent->pLeft == pRoot) pParent->pLeft = pLeftChild;
        else pParent->pRight = pLeftChild;
    }
    pLeftChild->pParent = pParent;

    if (pLeftChildRight = pLeftChild->pRight) {
        pLeftChildRight->pParent = pRoot;
    }
    pRoot->pLeft = pLeftChildRight;
    
    pLeftChild->pRight = pRoot;
    pRoot->pParent     = pLeftChild;

    pLeftChild->deltaH = 0;
    pRoot->deltaH      = 0;

    return pLeftChild;
}
/**
 * LR-Rotate
 *
 **/
AVLNode *LRRotate(AVLNode *pRoot)
{
    AVLNode *pLeftChild, *pLeftChildRight, *pLeftChildRightLeft, *pLeftChildRightRight, *pParent;

    pLeftChild      = pRoot->pLeft;
    pLeftChildRight = pLeftChild->pRight;

    if (pParent = pRoot->pParent) {
        if (pParent->pLeft == pRoot) pParent->pLeft = pLeftChildRight;
        else pParent->pRight = pLeftChildRight;
    }
    pLeftChildRight->pParent = pParent;

    if (pLeftChildRightLeft = pLeftChildRight->pLeft) {
        pLeftChildRightLeft->pParent = pLeftChild;
    }
    pLeftChild->pRight = pLeftChildRight;

    pLeftChildRight->pLeft = pLeftChild;
    pLeftChild->pParent    = pLeftChildRight;

    if (pLeftChildRightRight = pLeftChildRight->pRight) {
        pLeftChildRightRight->pParent = pRoot;
    }
    pRoot->pLeft = pLeftChildRightRight;

    pLeftChildRight->pRight = pRoot;
    pRoot->pParent          = pLeftChildRight;

    switch (pLeftChildRight->deltaH) {
    case 0:
        pLeftChild->deltaH = 0;
        pRoot->deltaH      = 0;
        break;
    case -1:
        pLeftChild->deltaH = 1;
        pRoot->deltaH      = 0;
        break;
    case +1:
        pLeftChild->deltaH = 0;
        pRoot->deltaH      = -1;
        break;
    }
    pLeftChildRight->deltaH = 0;

    return pLeftChildRight;
}
/**
 * RR-Rotate
 *
 **/
AVLNode *RRRotate(AVLNode *pRoot)
{
    AVLNode *pRightChild, *pRightChildLeft, *pParent;

    pRightChild = pRoot->pRight;

    if (pParent = pRoot->pParent) {
        if (pParent->pLeft == pRoot) pParent->pLeft = pRightChild;
        else pParent->pRight = pRightChild;
    }
    pRightChild->pParent = pParent;

    if (pRightChildLeft = pRightChild->pLeft) {
        pRightChildLeft->pParent = pRoot;
    }
    pRoot->pRight = pRightChild;
    
    pRightChild->pLeft = pRoot;
    pRoot->pParent     = pRightChild;

    pRightChild->deltaH = 0;
    pRoot->deltaH       = 0;

    return pRightChild;
}
/**
 * RL-Rotate
 *
 **/
AVLNode *RLRotate(AVLNode *pRoot)
{
    AVLNode *pRightChild, *pRightChildLeft, *pRightChildLeftLeft, *pRightChildLeftRight, *pParent;

    pRightChild     = pRoot->pRight;
    pRightChildLeft = pRightChild->pLeft;

    if (pParent = pRoot->pParent) {
        if (pParent->pLeft == pRoot) pParent->pLeft = pRightChildLeft;
        else pParent->pRight = pRightChildLeft;
    }
    pRightChildLeft->pParent = pParent;

    if (pRightChildLeftLeft = pRightChildLeft->pLeft) {
        pRightChildLeftLeft->pParent = pRoot;
    }
    pRoot->pRight = pRightChildLeftLeft;

    pRightChildLeft->pLeft = pRoot;
    pRoot->pParent         = pRightChildLeft;

    if (pRightChildLeftRight = pRightChildLeft->pRight) {
        pRightChildLeftRight->pParent = pRightChild;
    }
    pRightChild->pLeft = pRightChildLeftRight;

    pRightChildLeft->pRight= pRightChild;
    pRightChild->pParent   = pRightChildLeft;

    switch (pRightChildLeft->deltaH) {
    case 0:
        pRightChild->deltaH= 0;
        pRoot->deltaH      = 0;
        break;
    case -1:
        pRightChild->deltaH= 0;
        pRoot->deltaH      = 1;
        break;
    case +1:
        pRightChild->deltaH= -1;
        pRoot->deltaH      = 0;
        break;
    }
    pRightChildLeft->deltaH = 0;

    return pRightChildLeft;
}
AVLNode *findAVLNode(AVLNode *pRoot, int val)
{
    while(pRoot) {
        if (pRoot->val == val) break;
        else if (pRoot->val > val) pRoot = pRoot->pLeft;
        else pRoot = pRoot->pRight;
    }

    return pRoot;
}
AVLNode *findAVLNodeLastParent(AVLNode *pRoot, int val)
{
    AVLNode *pLastParent = NULL;

    while(pRoot) {
        pLastParent = pRoot;
        if (pRoot->val == val) break;
        else if (pRoot->val > val) pRoot = pRoot->pLeft;
        else pRoot = pRoot->pRight;
    }

    return pLastParent;
}
/**
 * Insert a new node into AVL-tree
 *
 **/
AVLNode* insertAVLNode(AVLNode **ppRoot, int val)
{
    AVLNode *pNodeLastParent, *pNode, *pParent;
    int iDirection = 0;

    pNodeLastParent = findAVLNodeLastParent(*ppRoot, val);
    if (!(pNode = findAVLNode(pNodeLastParent, val))) {
        pNode = (AVLNode *)malloc(sizeof(AVLNode));
        memset(pNode, 0, sizeof(AVLNode));
        if (!pNodeLastParent) {
            *pRoot = pNode;
        } else {
            if (pNodeLastParent->val > val) {
                pNodeLastParent->pLeft = pNode;
            } else {
                iDirection |= 0x1;
                pNodeLastParent->pRight = pNode;
            }
            pParent = pNodeLastParent;
            do {
                if (iDirection & 1) {
                    --pParent->deltaH;
                } else {
                    ++pParent->deltaH;
                }
                if (-2 == pParent->deltaH || 2 == pParent->deltaH) {
                    switch (iDirection) {
                    case 0://LL-Rotate
                        LLRotate(pParent); break;
                    case 1://RL-Rotate
                        RLRotate(pParent); break;
                    case 2://LR-Rotate
                        LRRotate(pParent); break;
                    case 3://RR-Rotate
                        RRRotate(pParent); break;
                    }
                } else {
                    iDirection <<= 1;
                    iDirection  &= 0x3;
                    if (pParent->pParent) {
                        if (pParent->pParent->pRight == pParent) iDirection |= 0x1;
                    }
                    pParent = pParent->pParent;
                }
            } while (pParent->deltaH);
        }
    }

    return pNode;
}
AVLNode *findLeftMaxNode(AVLNode *pNode)
{
    if (pNode) {
        if (pNode->pLeft) pNode = pNode->pLeft;
        while (pNode && pNode->pRight) pNode = pNode->pRight;
    }

    return pNode;
}
AVLNode *findRigtMinNode(AVLNode *pNode)
{
    if (pNode) {
        if (pNode->pRight) pNode = pNode->pRight;
        while (pNode && pNode->pLeft) pNode = pNode->pLeft;
    }

    return pNode;
}
/**
 * Delete a node from AVL-tree
 *
 **/
AVLNode* deleteAVLNode(AVLNode **ppRoot, int val)
{
    AVLNode *pNode, *pNodeLeftMax, *pNodeRight, *pParent;

    if (pNode = findAVLNode(*ppRoot, val)) {
        if (pNodeLeftMax = findLeftMaxNode(pNode)) {
        } else {//
            pParent
        }
    }

    return *ppRoot;
}

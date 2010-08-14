//
//  MainView.m
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import "MainView.h"

@implementation MainView
- (IBAction)hideConfig:(id)sender {
	[UIView beginAnimations:nil context:NULL];
	[UIView setAnimationDuration:1.0];
	[UIView setAnimationTransition:UIViewAnimationTransitionFlipFromLeft forView:self cache:YES];
	
	[config saveSettings];
	
    [config removeFromSuperview];
	[UIView commitAnimations];
}

- (IBAction)showConfig:(id)sender {
	[UIView beginAnimations:nil context:NULL];
	[UIView setAnimationDuration:1.0];
	[UIView setAnimationTransition:UIViewAnimationTransitionFlipFromRight forView:self cache:YES];

	[config loadSettings];
	
    [self addSubview:config];

	[UIView commitAnimations];
}



@end

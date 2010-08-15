//
//  PersonDetailsUIController.m
//
//  Created by Knut Erik Borgen on 15.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "PersonDetailsUIController.h"

@implementation PersonDetailsUIController


- (void)viewDidLoad {
	
	[super viewDidLoad];
	
	UIScrollView *tempScrollView=(UIScrollView *)self.view;
	
	tempScrollView.pagingEnabled = false;

	CGRect frame = commentsView.frame;
	frame.size.height = commentsView.contentSize.height;
	commentsView.frame = frame;
	
	tempScrollView.contentSize=CGSizeMake(320,frame.size.height + 380);

}


@end

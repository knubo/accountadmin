//
//  PersonUIViewController.m
//
//  Created by Knut Erik Borgen on 14.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "PersonUIViewController.h"
#import "PersonView.h"

@implementation PersonUIViewController


- (void) loadPeople {
	
	PersonView *v = (PersonView *)self.view;
	
	[v loadPeople];
}

@end
